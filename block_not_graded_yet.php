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

  /*function get_submitted_assignments($courseid) {
    global $DB;

    $sql = "SELECT cm.id AS cmid, a.name,  a.teamsubmission, grm.groupid as grid, u.username
              FROM {assign_submission} asb
              JOIN {assign} a      ON a.id = asb.assignment
              JOIN {course_modules} cm ON cm.instance = a.id
              JOIN {modules} md        ON md.id = cm.module
              JOIN {user} u            ON u.id = asb.userid
              JOIN {groups_members} grm			ON grm.userid = u.id
              JOIN {groups} gr					ON gr.id = grm.groupid
              LEFT JOIN {assign_user_mapping} um ON um.userid = u.id AND um.assignment = a.id
              WHERE
              asb.latest = 1 AND
              a.course = :courseid AND
              md.name = 'assign' AND
              asb.status = 'submitted' AND
              cm.deletioninprogress = 0
              ORDER BY a.name";
    $params = [
        'courseid' => $courseid
    ];

    return $DB->get_recordset_sql($sql, $params);
  }*/

  function get_number_submitted_assignments($courseid) {
    global $DB;

    $sql = "SELECT name, cmid, COUNT(*)
              FROM (SELECT DISTINCT a.name AS name, cm.id AS cmid, grm.groupid
                      FROM {assign_submission} asb
                      JOIN {assign} a      ON a.id = asb.assignment
                      JOIN {course_modules} cm ON cm.instance = a.id
                      JOIN {modules} md        ON md.id = cm.module
                      JOIN {user} u            ON u.id = asb.userid
                      JOIN {groups_members} grm                        ON grm.userid = u.id
                      JOIN {groups} gr                                        ON gr.id = grm.groupid
                      LEFT JOIN {assign_user_mapping} um ON um.userid = u.id AND um.assignment = a.id
                      WHERE
                      asb.latest = 1 AND
                      a.course = 2 AND
                      md.name = 'assign' AND
                      asb.status = 'submitted' AND
                      cm.deletioninprogress = 0 AND
                      a.teamsubmission = 1
                      UNION
                      SELECT a.name AS name, cm.id AS cmid, grm.groupid
                      FROM {assign_submission} asb
                      JOIN {assign} a      ON a.id = asb.assignment
                      JOIN {course_modules} cm ON cm.instance = a.id
                      JOIN {modules} md        ON md.id = cm.module
                      JOIN {user} u            ON u.id = asb.userid
                      JOIN {groups_members} grm  ON grm.userid = u.id
                      JOIN {groups} gr        ON gr.id = grm.groupid
                      LEFT JOIN {assign_user_mapping} um ON um.userid = u.id AND um.assignment = a.id
                      WHERE
                      asb.latest = 1 AND
                      a.course = 2 AND
                      md.name = 'assign' AND
                      asb.status = 'submitted' AND
                      cm.deletioninprogress = 0 AND
                      a.teamsubmission = 0) AS dist
              GROUP BY name, cmid";
    $params = [
        'courseid' => $courseid
    ];

    return $DB->get_recordset_sql($sql, $params);
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

    $modname = 'assign';
    foreach ($assignments as $assignment) {
      $icon = $OUTPUT->image_icon('icon', get_string('pluginname', $modname), $modname);
      $this->content->items[] = '<a href="'.$CFG->wwwroot.'/mod/assign/view.php?id='.$assignment->cmid.'&action=grading">'.$icon.$assignment->name.'('.$assignment->count.')'.'</a>';
    }


    return $this->content;
  }
}
 ?>
