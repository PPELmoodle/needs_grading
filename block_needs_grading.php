<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot.'/mod/assign/lib.php');
require_once($CFG->dirroot.'/lib/enrollib.php');

class block_needs_grading extends block_list {
  function init(){
    $this->title = get_string('pluginname', 'block_needs_grading');
  }

  /**
   *  Allow parameters in admin settings
   */
  function has_config() {
        return true;
  }
  
  function get_content(){
    global $CFG, $DB, $PAGE, $OUTPUT, $USER;
    require_once($CFG->dirroot.'/blocks/needs_grading/lib.php');
    //check if user has a group
    $my_group_activ = false;

    if($this->content !== NULL) {
      return $this->content;
    }

    $this->content = new stdClass;
    $this->content->text = '';
    $this->content->footer = '';
    $block_name = get_string('pluginname', 'block_needs_grading');
    $this->content->items[] = '<div class="ng-title">'.$block_name.'</div>';

    if (empty($this->instance)) {
        return $this->content;
    }


    $CFG->langstringcache = false;
    /* get enrolled courses of logged in user */
    $courses = enrol_get_users_courses($USER->id, true, NULL,  'visible ASC, sortorder DESC');
    $modname = 'assign';
    $needsgrading = false;
    $anypermission = false;

    /* make list of assignments which need grading */
    foreach ($courses as $course){
      $assignments = get_submissions_need_grading($course->id);
      $users_coursecontext= context_course::instance($course->id);
      
      /* check permission: if user is not allowed to grade assignments, not show the list */
      if(!(has_capability('mod/assign:grade', $users_coursecontext))) {
        continue;
      }
      else {
        $anypermission = true;
      }
      
      $block_text = '';
      $block_text_my_group ='';
      $sum = 0;
      $my_group_assignments_sum = 0;
      $block_prefix = '<details><summary class="ng-assigns">'.'<span class="coursename">'.$course->fullname.'</span>';
      
      $cm = groups_get_user_groups($course->id, $USER->id);
      $user_group = $cm[0]; 
      
      /* in case of group submission and if tutor is also assigned to one group to grade */
      if (sizeof($user_group) == 1) {
        $my_group_assignments = get_submissions_need_grading_for_my_group($course->id, $user_group[0]);
        $block_prefix_my_group = '<details><summary><span class="ng-assignsingroup">'.get_string('my_group', 'block_needs_grading').'</span>'; 
        
        foreach ($my_group_assignments as $mgs){
          $icon = $OUTPUT->image_icon('icon', get_string('pluginname', $modname), $modname);
          $block_text_my_group .= '<li><a href="'.$CFG->wwwroot.'/mod/assign/view.php?id='.$mgs->cmid.'&action=grading">'.$icon.$mgs->assignname.' </a>('.$mgs->count.')'.'</li>';
          $my_group_assignments_sum += $mgs->count;}
        
        $block_prefix_my_group .= ' <span class="sum">'.'('.$my_group_assignments_sum .')'.' </span></summary><ol>';
        $block_suffix_my_group = '</ol></details>';
        $my_group_activ = true;
      }
      else{
        $my_group_activ = false;
      }
      
      if ($assignments->key() != null) {
        $needsgrading = true;

        foreach ($assignments as $assignment) {
          $icon = $OUTPUT->image_icon('icon', get_string('pluginname', $modname), $modname);
          $block_text .= '<li><a href="'.$CFG->wwwroot.'/mod/assign/view.php?id='.$assignment->cmid.'&action=grading">'.$icon.$assignment->assignname.'</a> ('.$assignment->count.')'.'</li>';
          $sum += $assignment->count;
        }
      }
      else {
        $block_text .= '<li>Done.</li>';
      }
      $block_prefix .= ' <span class="sum">'.'('.$sum.')'.' </span></summary><ol>';
      $block_suffix ='</ol></details>'; 
      
      if($my_group_activ){
        $this->content->items[] = $block_prefix.$block_prefix_my_group.$block_text_my_group.$block_suffix_my_group.$block_text.$block_suffix;
      }
      else{
        $this->content->items[] = $block_prefix.$block_text.$block_suffix;
      }
    }

    if (!$needsgrading && $anypermission) {
      $this->content->items[] = get_string('noneedsgrading', 'block_needs_grading');
    }

    return $this->content;
  }
}
 ?>
