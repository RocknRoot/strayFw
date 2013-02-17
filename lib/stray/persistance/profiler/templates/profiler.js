function strayProfilerInit() {

  var maxAjaxLogs = 15;
  var ajaxLogIndex = 0;

  $(document).ready(function() {

    $('body').prepend($('#stray_profilerWrapper'));

    SyntaxHighlighter.highlight();

    $('#stray_profilerItems').show();

    var ajaxUrlLastLog = "/_stray/profiler/last";

    // catch ajax event
    var callAjaxInt = 0;
    $(document).ajaxSend(function(event, xhr, settings) {
      if (settings.url !== ajaxUrlLastLog) {
        callAjaxInt = callAjaxInt + 1;
      }
    });

    // When ajax request is complete
    $(document).ajaxComplete(function(event, xhr, settings) {
      if (settings.url !== ajaxUrlLastLog) {
        updateProfilerAjax(xhr);
        addProfilerAjax(event, xhr, settings);
        deleteOldAjaxLogs();
      }
    });

    // show compact profiler view
    $(document).on('click', '.stray_ajaxReport', function() {
      closeAjaxLogs();
      $('#view_' + $(this).attr('id')).show();
    });
  
    // close ajax log report
    $(document).on('click', '.stray_ajaxClose', function() {
      closeAjaxLogs();
    });

    // show/hide expand
    $('.stray_expand').click(function() {
      if ($(this).hasClass('stray_profilerItemSelected')) {
        closeExpand();
      }
      else {
        closeExpand();
        if (!$(this).is('#stray_ajax'))
          closeAjaxLogs();
        $(this).addClass('stray_profilerItemSelected');
        $(this).children('.stray_expandWrapper').css('min-width', $(this).outerWidth());
        $(this).children('.stray_expandWrapper').show();
      }
    });

  });
  
  /**
   * shift/pop functions
   */
  (function($) {

    $.fn.pop = function() {
      var top = this.get(-1);
      this.splice(this.length - 1, 1);
      return top;
    };

    $.fn.shift = function() {
      var bottom = this.get(0);
      this.splice(0, 1);
      return bottom;
    };

  })(jQuery);

  // update count ajax logs
  function updateProfilerAjax(xhr) {
    var element = '';
    if (xhr.status == 200) {
      element = 'Success';
    }
    else if (xhr.status == 404) {
      element = 'Notfound';
    }
    else {
      element = 'Error';
    }
    element = '.stray_ajaxStatus' + element;
    $(element).html(parseInt($(element).html()) + 1);
    ajaxLogIndex = ajaxLogIndex + 1;
    $('.stray_ajaxStatusCount').html(ajaxLogIndex);
  }

  // Add a link to show the result of an ajax call
  function addProfilerAjax(event, xhr, settings) {
    var expandAjax = $('#stray_ajax .stray_expandWrapper ul');
    expandAjax.show();

    $.ajax({
      url: ajaxUrlLastLog,
      dataType: "json",
      context: document.body
    }).done(function(response) {
      if (response.id !== undefined) {
        var expandItemLog = '<div class="stray_compactWrapper" id="view_' + response.id + '">' + response.html + '</div>';
        var expandItem = '<li class="stray_separator stray_ajaxReport" id="' + response.id + '"><span class="stray_profilerInfo ';
        if (xhr.status === 200) {
          expandItem += 'stray_profilerSuccess';
        }
        else {
          expandItem += 'stray_profilerError';
        }
        expandItem += '">' + xhr.status + '</span> ';
        expandItem += '#' + ajaxLogIndex + ' [' + settings.type + '] ' + settings.url;
        expandItem += '</li>';
        $('#stray_profilerWrapper').append(expandItemLog);
        expandAjax.append(expandItem);
        SyntaxHighlighter.highlight();
      }
    });

  }

  // delete old ajax reports
  function deleteOldAjaxLogs() {
    if ($('.stray_compactWrapper').length > maxAjaxLogs) {
      $($('.stray_ajaxReport').shift()).remove();
      $($('.stray_compactWrapper').shift()).remove();
    }
  }

  // close current expand
  function closeExpand() {
    $('.stray_profilerItemSelected').removeClass('stray_profilerItemSelected');
    $('.stray_expandWrapper').hide();
  }

  function closeAjaxLogs() {
    $('.stray_compactWrapper').hide();
  }

}

if (typeof define === 'function' && define.amd) {
  requirejs(['/js/jquery.js'], function($) {
    strayProfilerInit($);
  });
}
else {
  if (typeof jQuery == 'undefined') {
    var script = document.createElement('script');
    script.src = '/js/jquery.js';
    script.type = 'text/javascript';
    document.getElementsByTagName('head')[0].appendChild(script);
  }
  strayProfilerInit();
}
