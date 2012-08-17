/**
 * ************************************************************************
 * *                         Video Translator                            **
 * ************************************************************************
 * @package     mod                                                      **
 * @subpackage  Video Translator                                         **
 * @name        Video Translator                                         **
 * @copyright   oohoo.biz                                                **
 * @link        http://oohoo.biz                                         **
 * @author      Andrew McCann                                            **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later **
 * ************************************************************************
 * ************************************************************************ */

/**
 * This page handles all the javascript for the main view page.
 */


/**
 * This function makes subtitles show up in the side menu if video is not
 * fullscreen and on the bottom of the video if it is.
 */
var sub = function() {
    //Check if the video is full screen
    if(VideoJS("my_video_1").tag.player.isFullScreen) {        
        //If it is then show the subtitles.
        $('.vjs-subtitles').show();
    } else {
        //Otherwise hide them
        $('.vjs-subtitles').hide();
    }
    
    //Now move the subtitles to the side area.
    var i = VideoJS("my_video_1").subtitlesDisplay.el.innerHTML;
    var $_GET = getGet();
    $("#subtitlearea").load('updatevideo.php?id='+$_GET['id'], {
        'i':"" + i
    });
    return;
}

//Whenever the time updates redo the subtitles. (They need to update with time.)
VideoJS("my_video_1").ready(function(){
    VideoJS("my_video_1").addEvent('timeupdate', sub);
});

//Mose leaves the file list.
$('#file-list').mouseleave(function() {
    $(this).hide('slow'); 
});
//Mouse leaves the language list.
$('#lang-list').mouseleave(function() {
    $(this).hide('slow'); 
});

/*Hover animations*/
$('.file-link').hover(function() {
    $(this).css("font-size",16);
}, function() {
    $(this).css("font-size",13);
});
            
$('.lang-link').hover(function() {
    $(this).css("font-size",16);
                
}, function() {
    $(this).css("font-size",13);
});
             
$('.icon-holder').hover(function() {
    $(this).css("font-size",16);
                
}, function() {
    $(this).css("font-size",13);
});
            
/*Selection options*/
//File Selected
$('.file-link').click(function() {
    if(this.id == 'add-files') {
        loadPopup();
    } else {
        var $_GET = getGet();
        var info = $(this).attr("id").split('::');
        var url = info[0];
        //Load the video
        $("#myvidtag").fadeOut('fast').load('updatevideo.php?id='+$_GET['id'], {
            'vid': url,
            //Holds the url of the uploaded video.
            'sub': '',
            'lang':'Language',
            'lang_code': 'lg'
        },function() {
            VideoJS("my_video_1").addEvent('timeupdate', sub);
        }).fadeIn('fast');
    }
});

//Language selected
$('.lang-link').click(function() {
    var id = $(this).attr("id");
    var $_GET = getGet();

    $("#myvidtag").fadeOut('fast').load('updatevideo.php?id='+$_GET['id'], {
        'vid':$('#videourl').attr('url'), 
        'sub': id, 
        'lang':'Language', 
        'lang_code':'lg'
    }, function() {
        VideoJS("my_video_1").addEvent('timeupdate', sub);
    }).fadeIn('fast');
    
    $("#subtitlearea").load('updatevideo.php?id='+$_GET['id'], {
        'i':"" + i
    });
});


//Video menu
$('#lang').click(function() {
    $('#file-list').hide('fast');
    $('#lang-list').show('slow');
});

//Languge menu
$('#vid').click(function() {
    $('#lang-list').hide('fast');
    $('#file-list').show('slow');
});

$('#backgroundPopup').click(function() {
    closePopup();
});


//Load the "Add Files" popup.
function loadPopup() {
    $('#backgroundPopup').fadeIn('slow');
    
    var $_GET = getGet();
    
    //Load the addFiles.php page into the popup
    $('#dialog').load('addfiles.php?id=' + $_GET['id'], function() {
        $('#dialog').css({
            'position' : 'fixed',
            'top' : $(window).height()/2.0 - $('#dialog').height()/2.0,
            'left' : $(window).width()/2.0 - $('#dialog').width()/2.0,
            'overflow':'auto'
        });
        //Close the popup if close butotn is clicked,,
        $('#closeButton').click(function() {
            closePopup(); 
        });
        //Show the popup
        $('#dialog').fadeIn('slow');
    });
   
}

function closePopup() {
    $('#dialog').fadeOut('slow');
    $('#backgroundPopup').fadeOut('slow');
}

//Gets the php $_GET variable.
function getGet() {
    var parts = window.location.search.substr(1).split("&");
    var $_GET = {};
    for (var i = 0; i < parts.length; i++) {
        var temp = parts[i].split("=");
        $_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
    }

    return $_GET;
}

