<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot.'/mod/assign/lib.php');

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

  function get_number_submitted_assignments($courseid) {
    global $DB;

    $sql = "SELECT a.name, cm.id AS cmid, COUNT(cm.id)
              FROM {assign_submission} asb
              JOIN {assign} a      ON a.id = asb.assignment
              JOIN {course_modules} cm ON cm.instance = a.id
              JOIN {modules} md        ON md.id = cm.module
              JOIN {user} u            ON u.id = asb.userid
              LEFT JOIN {assign_user_mapping} um ON um.userid = u.id AND um.assignment = a.id
              WHERE
              asb.latest = 1 AND
              a.course = :courseid AND
              md.name = 'assign' AND
              asb.status = 'submitted' AND
              cm.deletioninprogress = 0
              GROUP BY a.name, cm.id";
    $params = [
        'courseid' => $courseid
    ];

    return $DB->get_records_sql($sql, $params);
  }

  function get_content(){
    global $CFG, $DB, $PAGE, $OUTPUT;

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
    $assignments = $this->get_number_submitted_assignments($course->id);
    $c = sizeof(assignments);
    //echo "<script>alert($c);</script>";

    $modname = 'assign';
    foreach ($assignments as $assignment) {
      $icon = $OUTPUT->image_icon('icon', get_string('pluginname', $modname), $modname);
      $this->content->items[] = '<a href="'.$CFG->wwwroot.'/mod/assign/view.php?id='.$assignment->cmid.'&action=grading">'.$icon.$assignment->name.'('.$assignment->count.')'.'</a>';
      //$this->content->items[] = $icon.$assignment->name.' ('.$assignment->count.')';
    }


    return $this->content;
  }
}
 ?>
