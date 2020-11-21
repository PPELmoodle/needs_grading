<?php
class block_not_graded_yet extends block_list {
  function init(){
    $this->title = get_string('pluginname', 'block_not_graded_yet');
  }

  function get_content(){
    global $CFG, $DB, $OUTPUT;

    if($this->content !== NULL) {
      return $this->content;
    }

    $this->content = new stdClass;
    $this->content->items = array();
    $this->content->icons = array();
    $this->content->footer = 'Footer here...';

    $course = $this->page->course;

    require_once($CFG->dirroot.'/course/lib.php');

    $modinfo = get_fast_modinfo($course);
    $modfullnames = array();

    $archetypes = array();

    foreach($modinfo->cms as $cm) {
      // Exclude activities that aren't visible or have no view link (e.g. label). Account for folder being displayed inline.
      if (!$cm->uservisible || (!$cm->has_view() && strcmp($cm->modname, 'folder') !== 0)) {
          continue;
      }
      if (array_key_exists($cm->modname, $modfullnames)) {
          continue;
      }
      if (!array_key_exists($cm->modname, $archetypes)) {
          $archetypes[$cm->modname] = plugin_supports('mod', $cm->modname, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER);
      }
      if ($archetypes[$cm->modname] == MOD_ARCHETYPE_RESOURCE) {
        if (!array_key_exists('resources', $modfullnames)) {
            $modfullnames['resources'] = get_string('resources');
        }
      } else {
          $modfullnames[$cm->modname] = $cm->modplural;
      }
    }

    core_collator::asort($modfullnames);

    foreach ($modfullnames as $modname => $modfullname) {
        if ($modname === 'resources') {
            $icon = $OUTPUT->pix_icon('icon', '', 'mod_page', array('class' => 'icon'));
            $this->content->items[] = '<a href="'.$CFG->wwwroot.'/course/resources.php?id='.$course->id.'">'.$icon.$modfullname.'</a>';
        } else {
            $icon = $OUTPUT->image_icon('icon', get_string('pluginname', $modname), $modname);
            $this->content->items[] = '<a href="'.$CFG->wwwroot.'/mod/'.$modname.'/index.php?id='.$course->id.'">'.$icon.$modfullname.'</a>';
        }
    }



    return $this->content;
  }
}
 ?>
