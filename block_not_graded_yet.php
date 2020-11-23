<?php

defined('MOODLE_INTERNAL') || die();
//require_once($CFG->dirroot.'/course/lib.php');

class block_not_graded_yet extends block_list {
  function init(){
    $this->title = get_string('pluginname', 'block_not_graded_yet');
  }

  /**
   *  Allow parameters in admin settings
   */
  function has_config() {
        return true;
  }

  /**
   * Which page types this block may appear on.
   * To show only in a course not on dashboard (from course moodlenavigation block)
   * @return array
   */
  function applicable_formats() {
      return [
              'site-index' => true,
              'course-view-*' => true
      ];
  }

  /**
     * Helper function to retrieve the assignment submission records for a given course.
     *
     * @param int $courseid     The course ID to get assignment submissions by.
     * @return array            Array of assignment submission details.
     * @throws dml_exception
     */
    protected function get_course_assignment_submissions($courseid) {
      global $DB;

      $sql = "SELECT s.id,
                     s.assignment,
                     s.userid,
                     s.timecreated,
                     s.timemodified,
                     s.numfiles,
                     s.data1,
                     s.data2,
                     s.grade,
                     s.submissioncomment,
                     s.format,
                     s.teacher,
                     s.timemarked,
                     s.mailed
                FROM {assignment} a
                JOIN {assignment_submissions} s ON s.assignment = a.id
               WHERE a.course = :courseid";
      $params = [
          'courseid' => $courseid
      ];

      return $DB->get_records_sql($sql, $params);
    }

  function get_content(){
    global $CFG, $DB, $PAGE;

    $course = $this->page->course;

    if($this->content !== NULL) {
      return $this->content;
    }

    $this->content = new stdClass;
    $this->content->text = '';
    $this->content->footer = '';

    if (empty($this->instance)) {
        return $this->content;
    }

    // retrive the assignment records for a given course.
    $assignments = $this->get_course_assignment_submissions($course->id);

    foreach ($assignments as $assignment) {
      $this->content->items[] = 'Assignment'.$assignment;
    }
    $this->content->items[] = 'Course ID is '.$course->id;


    return $this->content;
  }
}
 ?>
