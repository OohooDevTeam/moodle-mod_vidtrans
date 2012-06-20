<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of vidtrans
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage vidtrans
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or

if ($id) {
    $cm = get_coursemodule_from_id('vidtrans', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $vidtrans = $DB->get_record('vidtrans', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

$context = get_context_instance(CONTEXT_COURSE, $course->id);


require_login($course, true, $cm);
require_capability('mod/vidtrans:upload_video', get_context_instance(CONTEXT_MODULE, $cm->id));


global $DB;

$fs = get_file_storage();

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "  <head>\n";
echo "      <link rel=\"stylesheet\" type=\"text/css\" href=\"css/ui-darkness/jquery-ui-1.8.18.custom.css\"/>\n";
echo "      <link rel=\"stylesheet\" type=\"text/css\" href=\"css/jquery.dataTables.css\"/>\n";
echo "      <link rel=\"stylesheet\" type=\"text/css\" href=\"view.css\"/>\n";
echo "      <script src=\"jquery-1.7.1.js\"></script>\n";
echo "      <script src=\"js/jquery.dataTables.js\"></script>\n";
echo "      <script type=\"text/javascript\">\n";
echo "          $(document).ready(function() {\n";
echo "              $('#data-table').dataTable({'bJQueryUI': true});});\n";
echo "       </script>\n";
echo "  </head>\n";
echo "  <body>\n";

//Get information for table || Item id 0 means it has been processed and is now ready for viewing
$files = $fs->get_area_files($context->id, 'mod_vidtrans', 'vidfiles');

$vidnames = array();

foreach ($files as $file) {
    if ($file->get_mimetype() == 'audio/ogg'
            || strstr($file->get_mimetype(), 'video')) {

        $filename = $file->get_filepath() . substr($file->get_filename(), 0, strrpos($file->get_filename(), '.', -0));

        $pair = new stdClass;
        $pair->vid = $file;

        //Find subtitle file if it exists
        $pair->sub = null;

        if (!in_array($filename, $vidnames)) {
            array_push($vidnames, $filename);

            foreach ($files as $file2) {
                if ($file2->get_mimetype() == 'application/octet-stream' && $file->get_filepath() == $file2->get_filepath()
                        /* Make sure your grabbing the original file not the _ru or _fr files. */
                        && substr($file2->get_filename(), strpos($file2->get_filename(), '.', -0) + 1) == 'srt') {
                    $pair->sub = $file2;
                }
            }
            $vid_sub_pair[] = $pair;
        }
    }
}

//Build table
echo "  <form id='uploadform' method='post' enctype='multipart/form-data' action='upload.php?id=$id'\">\n";
echo "      <table id='data-table' style='text-align: center'>\n";
echo "          <thead>\n";
echo "              <tr>\n";
echo "                  <th> " . get_string('video_files', 'vidtrans') . " </th>\n";
echo "                  <th> " . get_string('supported_browsers', 'vidtrans') . " </th>\n";
echo "                  <th> " . get_string('subtitle_files', 'vidtrans') . " </th>\n";
echo "                  <th style: width='13'> " . get_string('edit', 'vidtrans') . " </th>\n";
echo "              </tr>\n";
echo "          </thead>\n";
echo "          <tbody>\n";

for ($i = 0; isset($vid_sub_pair) && $i < count($vid_sub_pair); $i++) {
    echo "              <tr>\n";

    $vidfile = $vid_sub_pair[$i]->vid;

    $url = $CFG->wwwroot . '/pluginfile.php/' . $vidfile->get_contextid() . '/' . 'mod_vidtrans' . '/' . 'vidfiles';

    $vid_name = substr($vidfile->get_filename(), 0, strrpos($vidfile->get_filename(), '.', -0));
    $vid_filepath = $vidfile->get_filepath();

    $vidurl = $url . $vidfile->get_filepath() . $vidfile->get_itemid() . '/' . $vidfile->get_filename();

    $subfile = $vid_sub_pair[$i]->sub;
    if ($subfile != null) {
        $sub_name = $subfile->get_filename();
        $suburl = $url . $subfile->get_filepath() . $subfile->get_itemid() . '/' . $subfile->get_filename();
    } else {
        $sub_name = '';
        $suburl = '';
    }

    echo "              <td> <a href='$vidurl'>$vid_name</a></td>\n";

    echo "              <td>\n";


    //Find all succesfully converted types. (If images are clicked we will allow for individual uploads.
    $filename = substr($vidfile->get_filename(), 0, strrpos($vidfile->get_filename(), '.', -0));

    $fileinfo = array(
        'contextid' => $vidfile->get_contextid(),
        'component' => 'mod_vidtrans',
        'filearea' => 'vidfiles',
        'itemid' => 0,
        'filepath' => $vidfile->get_filepath(),
        'filename' => $filename,
        'mimetype' => $vidfile->get_mimetype());

    print_conversion_status('.mp4', $fileinfo, array('ie', 'safari'));

    print_conversion_status('.ogg', $fileinfo, array('firefox', 'opera'));

    print_conversion_status('.webm', $fileinfo, array('chrome'));

    echo "              </td>";

    echo "              <td> <a href='$suburl'>$sub_name</a> </td>\n";



    //Add in the remove button.
    $vid_name = substr($vidfile->get_filename(), 0, strrpos($vidfile->get_filename(), '.', -0));
    $vid_filepath = $vidfile->get_filepath();

    echo "              <td> <img id='$vid_name::$vid_filepath' class='remove_button' src='images/remove.png'></img> </td>\n";
    echo "              </tr>\n";
}

echo "          </tbody>\n";

echo "              <tr>\n";
echo "                  <td>\n";
echo "                      <input class='fileup' type=\"file\" name=\"vidfile\"> \n";
echo "                  </td>\n";
echo "                  <td></td>\n";
echo "                  <td>\n";
echo "                      <input class='fileup' type=\"file\" name=\"subfile\">\n";
echo "                  </td>\n";

echo "                  <td>\n";
echo '                      <input id=\'upload_button\' type="image" name="submit" src="images/upload.png">';
echo "                  </td>\n";
echo "              </tr>\n";
echo "      </table>\n";
echo "  </form>";
echo "      <iframe id='upload_target', name='upload_target' src ='' style='display:none'></iframe>";
echo "  </body>\n";
echo <<<"SCRIPT"
    <script>
        
        $('.remove_button').click(function(){
            var info = $(this).attr('id').split('::');

            $('#upload_target').load('delete.php?id=' + $id, {
                'filename' : info[0],
                'filepath' : info[1]
            });
            
            window.location.reload()
        });

</script>
SCRIPT;
echo "</html>";



function print_conversion_status($extension, $fileinfo, $browsers) {
    global $fs;

    //If Ogg was converted sucessfully
    $fileinfo['ext'] = $extension;
    $json_info = json_encode($fileinfo);

    $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea']
            , $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename'] . $fileinfo['ext']);
    if ($file && $file->get_filesize() != 0) {
        foreach ($browsers as $browser) {
            echo "              <img id='$json_info' class='indvl-up' src='images/$browser.png' width='13' height='13'></img>";
        }
    } else {
        $waiting_files = $fs->get_area_files($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], '2', "sortorder, itemid, filepath, filename", false);
        $processing_files = $fs->get_area_files($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], '1', "sortorder, itemid, filepath, filename", false);

        $in_processing_area = false;

        foreach ($waiting_files as $aFile) {

            if ($aFile->get_filepath() == $fileinfo['filepath']) {
                $in_processing_area = true;
                break;
            }
        }

        foreach ($processing_files as $aFile) {
            if ($in_processing_area)
                break;
            if ($aFile->get_filepath() == $fileinfo['filepath']) {
                $in_processing_area = true;
                break;
            }
        }

        if (!$file && $in_processing_area) {
            foreach ($browsers as $browser) {
                echo "              <img id='$json_info' class='indvl-up' src='images/$browser.png' width='13' height='13' style='opacity:0.4; filter:alpha(opacity=100);'></img>";
            }
        } else {
            foreach ($browsers as $browser) {
                echo "              <img id='$json_info' class='indvl-up' src='images/$browser-x.png' width='13' height='13'></img>";
            }
        }
    }
}
?>

