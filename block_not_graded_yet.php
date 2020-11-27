<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot.'/mod/assign/lib.php');
require_once($CFG->dirroot.'/lib/enrollib.php');

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
    global $CFG, $DB, $PAGE, $OUTPUT, $USER;
    require_once($CFG->dirroot.'/blocks/not_graded_yet/lib.php');

    if($this->content !== NULL) {
      return $this->content;
    }

    $this->content = new stdClass;
    $this->content->text = '';
    $this->content->footer = '';

    if (empty($this->instance)) {
        return $this->content;
    }


    $CFG->langstringcache = false;
    $courses = enrol_get_users_courses($USER->id);
    $modname = 'assign';
    $needsgrading = false;


    foreach ($courses as $course){
      $assignments = get_submissions_need_grading($course->id);

      $block_text = '';
      $sum = 0;
      $block_prefix = '<details><summary><a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'.$course->fullname.'</a>';
      if (sizeof($assignments) > 0) {
        $needsgrading = true;

        foreach ($assignments as $assignment) {
          $icon = $OUTPUT->image_icon('icon', get_string('pluginname', $modname), $modname);
          $block_text .= '<li><a href="'.$CFG->wwwroot.'/mod/assign/view.php?id='.$assignment->cmid.'&action=grading">'.$icon.$assignment->name.'('.$assignment->count.')'.'</a></li>';
          $sum += $assignment->count;
        }
      }
      else {
        //$this->content->items[] = get_string('noneedsgrading', 'block_not_graded_yet');
      }
      $block_prefix .= ' ('.$sum.') </summary><ol>';
      $block_suffix ='</ol></details>';
      $this->content->items[] = $block_prefix.$block_text.$block_suffix;
    }

    if (!$needsgrading) {
      $this->content->items[] = get_string('noneedsgrading', 'block_not_graded_yet');
    }

    return $this->content;
  }
}
 ?>
