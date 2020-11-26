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

  function get_content(){
    global $CFG, $DB, $PAGE, $OUTPUT;
    require_once($CFG->dirroot.'/blocks/not_graded_yet/lib.php');

    $course = $this->page->course;
    $courseid = $course->id;

    if($this->content !== NULL) {
      return $this->content;
    }

    $this->content = new stdClass;
    $this->content->text = '';
    $this->content->footer = '';

    if (empty($this->instance)) {
        return $this->content;
    }

    // get assignments with each number of submissions which need grading for a given course.
    $assignments = get_submissions_need_grading($courseid);


    $modname = 'assign';
    foreach ($assignments as $assignment) {
      $icon = $OUTPUT->image_icon('icon', get_string('pluginname', $modname), $modname);
      $this->content->items[] = '<a href="'.$CFG->wwwroot.'/mod/assign/view.php?id='.$assignment->cmid.'&action=grading">'.$icon.$assignment->name.'('.$assignment->count.')'.'</a>';
    }


    return $this->content;
  }
}
 ?>
