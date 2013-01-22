if (typeof jQuery != 'undefined') {
  $(document).ready(function() {

    $('body').prepend($('#stray_profilerWrapper'));
    $('#stray_profilerWrapper').show();

    var ajax_last_call = "/_stray/profiler/last";

    // catch ajax event
    var callAjaxInt = 0;
    $(document).ajaxSend(function(event, xhr, settings) {
      if (settings.url !== ajax_last_call) {
        callAjaxInt = callAjaxInt + 1;
        $('.stray_ajaxStatus').html(callAjaxInt);
        $('.stray_ajaxInfo').html('Loading');
      }
    });
    $(document).ajaxSuccess(function(event, xhr, settings) {
      if (settings.url !== ajax_last_call) {
        $('.stray_ajaxInfo').html('Completed successfully');
        addProfilerAjax(xhr, settings);
      }
    });
    $(document).ajaxError(function(event, xhr, settings) {
      if (settings.url !== ajax_last_call) {
        $('.stray_ajaxInfo').html('Completed with error');
        addProfilerAjax(xhr, settings);
      }
    });

    // Add a link to show the result of an ajax call
    function addProfilerAjax(xhr, settings) {
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
          expandItem += settings.url;
          expandItem += '</li>';
          $('#stray_profilerWrapper').append(expandItemLog);
          expandAjax.append(expandItem);
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
      closeExpand();
      if(!$(this).is('#stray_ajax'))
        closeAjaxLogs();
      $(this).addClass('stray_profilerItemSelected');
      $(this).children('.stray_expandWrapper').css('min-width', $(this).outerWidth());
      $(this).children('.stray_expandWrapper').show();
    });

    // close current expand
    function closeExpand() {
      $('.stray_profilerItemSelected').removeClass('stray_profilerItemSelected');
      $('.stray_expandWrapper').hide();
    }
  
    function closeAjaxLogs() {
      $('.stray_compactWrapper').hide();
    }

  });
} else {
  $('#jqueryNotLoaded').show();
}