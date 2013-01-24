var maxAjaxLogs = 5;
var ajaxLogIndex = 0;

if (typeof jQuery != 'undefined') {
  $(document).ready(function() {

    SyntaxHighlighter.highlight();

    $('body').prepend($('#stray_profilerWrapper'));
    $('#stray_profilerWrapper').show();

    var ajax_last_call = "/_stray/profiler/last";

    // catch ajax event
    var callAjaxInt = 0;
    $(document).ajaxSend(function(event, xhr, settings) {
      if (settings.url !== ajax_last_call) {
        callAjaxInt = callAjaxInt + 1;
        $('.stray_ajaxStatus').html(callAjaxInt);
        $('.stray_ajaxInfo').html('loading');
      }
    });

    $(document).ajaxComplete(function(event, xhr, settings) {
      ajaxLogIndex++;
      if (settings.url !== ajax_last_call) {
        var resultInfo = '';
        if (xhr.status == 200)
          resultInfo = 'Complete';
        else if (xhr.status == 404)
          resultInfo = 'Not found';
        else
          resultInfo = 'Error';
        $('.stray_ajaxInfo').html(resultInfo);
        addProfilerAjax(event, xhr, settings);
        deleteOldAjaxLogs();
      }
    });

    // Add a link to show the result of an ajax call
    function addProfilerAjax(event, xhr, settings) {
      var expandAjax = $('#stray_ajax .stray_expandWrapper ul');
      expandAjax.show();

      $.ajax({
        url: ajax_last_call,
        dataType: "json",
        context: document.body
      }).done(function(response) {
        if (response.id !== undefined) {
          var expandItemLog = '<div class="stray_compactWrapper" id="view_' + response.id + '">' + response.html + '</div>';
          var expandItem = '<li class="stray_separator stray_ajaxReport" id="' + response.id + '"><span class="stray_profilerInfo ';
          if (xhr.status === 200)
            expandItem += 'stray_profilerSuccess';
          else
            expandItem += 'stray_profilerError';
          expandItem += '">' + xhr.status + '</span> ';
          expandItem += '#' + ajaxLogIndex + ' [' + settings.type + '] ' + settings.url;
          expandItem += '</li>';
          $('#stray_profilerWrapper').append(expandItemLog);
          expandAjax.append(expandItem);
          SyntaxHighlighter.highlight();
        }
      });

    }
    // show compact profiler view
    $('#stray_ajax').on('click', '.stray_ajaxReport', function() {
      closeAjaxLogs();
      $('#view_' + $(this).attr('id')).show();
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

    // delete old ajax reports
    function deleteOldAjaxLogs() {
      if ($('.stray_compactWrapper').length > maxAjaxLogs) {
        $($('.stray_ajaxReport').pop()).remove();
        $($('.stray_compactWrapper').pop()).remove();
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

  });
}
else {
  $('#jqueryNotLoaded').show();
}
