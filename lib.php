<?php
function get_submissions_need_grading($courseid) {
  global $DB;

  $sql = "SELECT name, cmid, COUNT(*) as count
            FROM (SELECT DISTINCT a.name AS name, cm.id AS cmid, grm.groupid
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
                    (asg.grade is NULL OR asg.grade < 0)) AS teamsub
                    group by name, cmid
              UNION
              SELECT a.name AS name, cm.id AS cmid, COUNT(*) as count
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
                    GROUP BY a.name, cm.id";
  $params = [
      'courseid1' => $courseid,
      'courseid2' => $courseid
  ];

  return $DB->get_recordset_sql($sql, $params);
}
function get_submissions_need_grading_for_my_group($courseid,$groupid){
  global $DB;
  
  $sql = "SELECT assignmentname, cmid, count
           FROM (SELECT  assignmentname, cmid, COUNT(*) as count, assignid 
                  FROM (SELECT cm.id AS cmid, a.name AS assignmentname, a.id AS assignid
                   FROM {assign_submission} asb
                   JOIN {assign} a ON a.id = asb.assignment
                   LEFT JOIN {assign_grades} ag ON ag.assignment = a.id
                                                   AND asb.assignment = ag.assignment
                                                   AND asb.userid = ag.userid
                                                   AND asb.attemptnumber = ag.attemptnumber
                   JOIN {user} u ON u.id = asb.userid AND u.deleted = 0
                   JOIN {course} c ON c.id = :courseid1 AND c.id = a.course
                   JOIN {course_modules} cm ON a.course = cm.course AND cm.instance = a.id 
                   JOIN {modules} md ON md.id = cm.module
                   JOIN {grade_items} gi ON a.course = gi.courseid AND gi.itemmodule = 'assign' AND a.id = gi.iteminstance
                   LEFT JOIN {groups_members} grm  ON grm.userid = asb.userid
                   LEFT JOIN {groups} gr  ON gr.id = grm.groupid
                   WHERE
                   asb.latest = 1
                   AND asb.timemodified IS NOT NULL
                   AND a.teamsubmission = 0
                   AND asb. STATUS = 'submitted'
                   AND md.name = 'assign' 
                   AND grm.groupid = :groupid1
                   AND (asb.timemodified >= ag.timemodified
                        OR ag.timemodified IS NULL
                        OR ag.grade IS NULL)
                   Group By u.id, a.id) AS team 
                   Group By team.assignid
             UNION
             SELECT assignmentname, cmid, COUNT(*) AS count, assignid
               FROM (SELECT assignmentname, assignid, groupid,cmid
                      FROM (SELECT cm.id AS cmid, a.name AS assignmentname, a.id AS assignid, u.id AS userid, grm.groupid AS groupid
                             FROM {assign_submission} asb
                             JOIN {assign} a ON a.id = asb.assignment
                             LEFT JOIN {assign_grades} ag ON ag.assignment = a.id
                                                          AND asb.assignment = ag.assignment
                                                          AND asb.userid = ag.userid
                                                          AND asb.attemptnumber = ag.attemptnumber
                             JOIN {user} u ON u.id = asb.userid AND u.deleted = 0
                             JOIN {course} c ON c.id = :courseid2 AND c.id = a.course
                             JOIN {course_modules} cm ON a.course = cm.course AND cm.instance = a.id 
                             JOIN {modules} md ON md.id = cm.module
                             JOIN {grade_items} gi ON a.course = gi.courseid AND gi.itemmodule = 'assign' AND a.id = gi.iteminstance
                             LEFT JOIN {groups_members} grm  ON grm.userid = asb.userid
                             LEFT JOIN {groups} gr  ON gr.id = grm.groupid
                             WHERE
                             asb.latest = 1
                             AND asb.timemodified IS NOT NULL
                             AND a.teamsubmission = 1
                             AND asb. STATUS = 'submitted'
                             AND md.name = 'assign' 
                             AND grm.groupid = :groupid2
                             AND (asb.timemodified >= ag.timemodified
                                  OR ag.timemodified IS NULL 
                                  OR ag.grade IS NULL)
                             AND grm.groupid IS NOT NULL 
                             Group By grm.groupid,a.id) AS team 
                             Group BY team.assignid, team.userid) AS sum 
                             Group BY sum.assignid) AS assignmenglist
                             ORDER BY assignmenglist.assignid";
    
    $params = [
        'courseid1' => $courseid,
        'groupid1' => $groupid,
        'courseid2' => $courseid,
        'groupid2' => $groupid
    ];
    
     return $DB->get_recordset_sql($sql, $params);
}

 ?>
