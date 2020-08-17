var wkc
wkc = jQuery.noConflict();

(function (wkc) {
  wkc(document).ready(function () {


    wkc("#mpbs_sender_text_color, #mpbs_sender_chat_timetxtcolor, #mpbs_sender_chat_bgcolor,#mpbs_receiver_text_color, #mpbs_receiver_chat_timetxtcolor, #mpbs_receiver_chat_bgcolor, #mpbs_admin_chat_stript_color, #mpbs_customer_chat_stript_color ").wpColorPicker();
    
    // start stop server
    wkc('#mpbs-start-stop-server').on('click', function () {
      if (wkc(this).data('action') == 'start') {
        mpbsStartServer()
      }
      if (wkc(this).data('action') == 'stop') {
        mpbsStopServer()
      }
    })

    // start server
    function mpbsStartServer () {
      var thisElm = wkc(this)
      var hostName = wkc('#mpbs_host_name').val()
      var port = wkc('#mpbs_port_num').val()
      wkc.ajax({
        type: 'post',
        url: mpbs_admin_script_object.ajaxurl,
        data: {
          'action': 'mpbs_start_server',
          'nonce': mpbs_admin_script_object.admin_ajax_nonce,
          'hostname': hostName,
          'port': port
        },
        beforeSend: function () {
          wkc('body').append('<div class="mpbs-loader"><div class="mpbs-spinner mpbs-skeleton"><!--////--></div></div>')
          wkc('.mpbs-loader').css('display', 'inline-block')
          wkc('body').css('overflow', 'hidden')
        },
        complete: function () {
          setTimeout(function () {
            wkc('body').css('overflow', 'auto')
            wkc('.mpbs-loader').remove()
          }, 1500)
        },
        success: function (response) {
          response = JSON.parse(response)
          if (!response.error) {
            setTimeout(function () {
              wkc('#responsedialog').children('p').html(response.message)
              wkc('#responsedialog').dialog({
                title: response.status,
                buttons: {
                  OK: function () {
                    wkc(this).dialog('close')
                    location.reload()
                  }
                }
              })
            }, 1500)
          } else {
            alert(response.message)
          }
        }
      })
    }

    // stop server
    function mpbsStopServer () {
      var thisElm = wkc(this)
      var hostName = wkc('#mpbs_host_name').val()
      var port = wkc('#mpbs_port_num').val()
      wkc.ajax({
        type: 'post',
        url: mpbs_admin_script_object.ajaxurl,
        data: {
          'action': 'mpbs_stop_server',
          'nonce': mpbs_admin_script_object.admin_ajax_nonce,
          'hostname': hostName,
          'port': port
        },
        beforeSend: function () {
          wkc('body').append('<div class="mpbs-loader"><div class="mpbs-spinner mpbs-skeleton"><!--////--></div></div>')
          wkc('.mpbs-loader').css('display', 'inline-block')
          wkc('body').css('overflow', 'hidden')
        },
        complete: function () {
          setTimeout(function () {
            wkc('body').css('overflow', 'auto')
            wkc('.mpbs-loader').remove()
          }, 1500)
        },
        success: function (response) {
          response = JSON.parse(response)
          console.log(response)
          if (!response.error) {
            setTimeout(function () {
              wkc('#responsedialog').children('p').html(response.message)
              wkc('#responsedialog').dialog({
                title: response.status,
                buttons: {
                  OK: function () {
                    wkc(this).dialog('close')
                    location.reload()
                  }
                }
              })
            }, 1500)
          } else {
            alert(response.message)
          }
        }
      })
    }

    // https enabled/disabled
    if (wkc('#mpbs_https_enabled').length) {
      wkc('#mpbs_https_enabled').on('change', function () {
        wkc('.mpbs_display_server_file_rows').toggle()
      })

      // file upload customised
      var inputs = document.querySelectorAll('input[type="file"]')

      Array.prototype.forEach.call(inputs, function (input) {
        var label = input.nextElementSibling
        var labelVal = label.innerHTML
        input.addEventListener('change', function (e) {
          var fileName = ''
          if (this.files) {
            fileName = this.files[0].name
          }
          if (fileName) {
            label.innerHTML = '<span class="dashicons dashicons-upload"></span>' + fileName
          } else {
            label.innerHTML = labelVal
          }
        })
      })

      // file remove
      wkc(document).on('click', '.mpbs_remove_file', function (evt) {
        evt.preventDefault()
        if (confirm(wkc(this).data('confirm'))) {
          var option = wkc(this).data('option')
          wkc(this).parent().siblings('input.mpbs_remove_input').val(option)
          wkc(this).parent().html('<span class="dashicons dashicons-upload"></span>');
        }
      })   
    }



    wkc(document).on('change', '#mpbs_seller_id', function (evt) {
      evt.preventDefault()

      var seller_id = wkc('#mpbs_seller_id').val();

      wkc.ajax({
        type: 'post',
        url: mpbs_admin_script_object.ajaxurl,
        data: {
          'action': 'mpbs_get_buyer_list',
          'nonce': mpbs_admin_script_object.admin_ajax_nonce,
          'seller_id': seller_id,
        },
        beforeSend: function () {
          wkc('body').append('<div class="mpbs-loader"><div class="mpbs-spinner mpbs-skeleton"><!--////--></div></div>')
          wkc('.mpbs-loader').css('display', 'inline-block')
          wkc('body').css('overflow', 'hidden')
        },
        complete: function () {
          setTimeout(function () {
            wkc('body').css('overflow', 'auto')
            wkc('.mpbs-loader').remove()
          }, 1500)
        },
        success: function (response) {
          
          response = JSON.parse(response)
          console.log(response);
          if (!response.error) {
            wkc('#mpbs_buyer_id').html(response.message);
          }
          
        }
      })
      

    });
  })


})(wkc)
