<?php
function get_submissions_need_grading($courseid) {
  global $DB;

  $sql = "SELECT name, cmid, COUNT(*) as count
            FROM (SELECT DISTINCT a.name AS name, cm.id AS cmid, grm.groupid
                    FROM {assign_submission} asb
                    JOIN {assign} a      ON a.id = asb.assignment
                    JOIN {course_modules} cm ON cm.instance = a.id
                    JOIN {modules} md        ON md.id = cm.module
                    JOIN {user} u            ON u.id = asb.userid
                    JOIN {groups_members} grm                        ON grm.userid = u.id
                    JOIN {groups} gr                                        ON gr.id = grm.groupid
                    LEFT JOIN {assign_user_mapping} um ON um.userid = u.id AND um.assignment = a.id
                    LEFT JOIN {assign_grades} asg ON asg.userid = asb.userid AND asg.assignment = asb.assignment
                    WHERE
                    asb.latest = 1 AND
                    a.course = :courseid1 AND
                    md.name = 'assign' AND
                    asb.status = 'submitted' AND
                    cm.deletioninprogress = 0 AND
                    a.teamsubmission = 1 AND
                    (asg.userid is NULL OR asg.assignment is NULL OR asg.grade is NULL)
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
                    LEFT JOIN {assign_grades} asg ON asg.userid = asb.userid AND asg.assignment = asb.assignment
                    WHERE
                    asb.latest = 1 AND
                    a.course = :courseid2 AND
                    md.name = 'assign' AND
                    asb.status = 'submitted' AND
                    cm.deletioninprogress = 0 AND
                    a.teamsubmission = 0 AND
                    (asg.userid is NULL OR asg.assignment is NULL OR asg.grade is NULL)) AS dist
                    GROUP BY name, cmid";
  $params = [
      'courseid1' => $courseid,
      'courseid2' => $courseid
  ];

  return $DB->get_recordset_sql($sql, $params);
}
 ?>
