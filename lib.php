<?php
function get_submissions_need_grading($courseid) {
     global $DB;

     $sql = "SELECT assignname, cmid, COUNT(*) as count
               FROM {assign_submission} asb
               JOIN (SELECT DISTINCT a.name AS assignname, cm.id AS cmid, grm.groupid AS gid, asb.assignment AS assignid
                    FROM {assign_submission} asb
                    JOIN {assign} a ON a.id = asb.assignment
                    JOIN {course_modules} cm ON cm.instance = a.id
                    JOIN {modules} md ON md.id = cm.module
                    JOIN {groups_members} grm ON grm.userid = asb.userid
                    JOIN {groups} gr  ON gr.id = grm.groupid
                    LEFT JOIN {assign_grades} asg ON asg.userid = asb.userid AND asg.assignment = asb.assignment
                    WHERE
                    a.course = :courseid1 AND
                    gr.courseid = a.course AND
                    md.name = 'assign' AND
                    asb.status = 'submitted' AND
                    cm.deletioninprogress = 0 AND
                    a.teamsubmission = 1 AND
                    (asg.grade is NULL OR asg.grade < 0)) AS team
                    ON asb.userid = 0 AND asb.groupid = team.gid AND asb.assignment = team.assignid
                    group by assignname, cmid
          UNION
               SELECT a.name AS assignname, cm.id AS cmid, COUNT(*) as count
                    FROM {assign_submission} asb
                    JOIN {assign} a ON a.id = asb.assignment
                    JOIN {course_modules} cm ON cm.instance = a.id
                    JOIN {modules} md ON md.id = cm.module
                    LEFT JOIN {assign_grades} asg ON asg.userid = asb.userid AND asg.assignment = asb.assignment
                    WHERE
                    a.course = :courseid2 AND
                    md.name = 'assign' AND
                    asb.status = 'submitted' AND
                    cm.deletioninprogress = 0 AND
                    a.teamsubmission = 0 AND
                    (asg.grade is NULL OR asg.grade < 0)
                    GROUP BY assignname, cmid";

  $params = [
      'courseid1' => $courseid,
      'courseid2' => $courseid
  ];

  return $DB->get_recordset_sql($sql, $params);
}


function get_submissions_need_grading_for_my_group($courseid, $groupid){
     global $DB;

     $sql = "SELECT assignname, cmid, COUNT(*) as count
               FROM {assign_submission} asb
               JOIN (SELECT DISTINCT a.name AS assignname, cm.id AS cmid, grm.groupid AS gid, asb.assignment AS assignid
                    FROM {assign_submission} asb
                    JOIN {assign} a ON a.id = asb.assignment
                    JOIN {course_modules} cm ON cm.instance = a.id
                    JOIN {modules} md ON md.id = cm.module
                    JOIN {groups_members} grm ON grm.userid = asb.userid
                    JOIN {groups} gr  ON gr.id = grm.groupid
                    LEFT JOIN {assign_grades} asg ON asg.userid = asb.userid AND asg.assignment = asb.assignment
                    WHERE
                    a.course = :courseid1 AND
                    grm.groupid = :groupid1 AND
                    gr.courseid = a.course AND
                    md.name = 'assign' AND
                    asb.status = 'submitted' AND
                    cm.deletioninprogress = 0 AND
                    a.teamsubmission = 1 AND
                    (asg.grade is NULL OR asg.grade < 0)) AS team
                    ON asb.userid = 0 AND asb.groupid = team.gid AND asb.assignment = team.assignid
                    group by assignname, cmid
          UNION
               SELECT a.name AS assignname, cm.id AS cmid, COUNT(*) as count
                    FROM {assign_submission} asb
                    JOIN {assign} a ON a.id = asb.assignment
                    JOIN {course_modules} cm ON cm.instance = a.id
                    JOIN {modules} md ON md.id = cm.module
                    JOIN {groups_members} grm ON grm.userid = asb.userid
                    LEFT JOIN {assign_grades} asg ON asg.userid = asb.userid AND asg.assignment = asb.assignment
                    WHERE
                    a.course = :courseid2 AND
                    grm.groupid = :groupid2 AND
                    md.name = 'assign' AND
                    asb.status = 'submitted' AND
                    cm.deletioninprogress = 0 AND
                    a.teamsubmission = 0 AND
                    (asg.grade is NULL OR asg.grade < 0)
                    GROUP BY assignname, cmid
          ORDER BY cmid";
    
    $params = [
        'courseid1' => $courseid,
        'groupid1' => $groupid,
        'courseid2' => $courseid,
        'groupid2' => $groupid
    ];
    
     return $DB->get_recordset_sql($sql, $params);
}
 ?>
