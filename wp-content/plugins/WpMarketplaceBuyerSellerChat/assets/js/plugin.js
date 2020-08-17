var $wkc
$wkc = jQuery.noConflict();

(function ($wkc) {
  $wkc(document).ready(function () {
    $wkc(document).on('click', '.mpbs-chatbox-top-bar', function (event) {
      var thisElm = $wkc(this)
      $wkc(thisElm).parent('.mpbs-chatbox-container').toggleClass('mpbs-chatbox-hidden')

      if ($wkc(event.target).hasClass('mpbs-box-controls close')) {
        if ($wkc(event.target).parents('#mpbs-chat-window-container').length) {
          chatbox--
        }
        $wkc(thisElm).parent('.mpbs-chatbox-container').remove()
      }
      $wkc(thisElm).find('.mpbs-box-controls.maximize').toggleClass('minimize')
    })

    // toggle display config options
    $wkc(document).on('click', '.mpbs-history-clock', function () {
      $wkc(this).children('.mpbs-history-options').toggle()
      $wkc(this).parent().siblings('.mpbs-profile-setting').children().children('.mpbs-chat-setting-options').css('display', 'none')
      $wkc(this).parent().siblings('.mpbs-user-select-status').children().children('.mpbs-chat-status-options').css('display', 'none')
    })

    $wkc(document).on('click', '.mpbs-history-clock-seller', function () {
      $wkc(this).children('.mpbs-history-options-seller').toggle()
       })

    $wkc(document).on('click', '.mpbs-chat-status', function () {
      $wkc(this).children('.mpbs-chat-status-options').toggle()
      $wkc(this).parent().siblings('.mpbs-previous-history').children().children('.mpbs-history-options').css('display', 'none')
      $wkc(this).parent().siblings('.mpbs-profile-setting').children().children('.mpbs-chat-setting-options').css('display', 'none')
    })

    $wkc(document).on('click', '.mpbs-chat-setting', function () {
      $wkc(this).children('.mpbs-chat-setting-options').toggle()
      $wkc(this).parent().siblings('.mpbs-previous-history').children().children('.mpbs-history-options').css('display', 'none')
      $wkc(this).parent().siblings('.mpbs-user-select-status').children().children('.mpbs-chat-status-options').css('display', 'none')
    })

    // toggle profile configuration box
    $wkc(document).on('click', '#buyerProfileSetting', function () {
      $wkc('.mpbs-profile-setting-overlay').show()
      $wkc('.mpbs-profile-setting-box').show()
    })

    $wkc(document).on('click', '.mpbs-profile-setting-box .mpbs-close-box', function () {
      $wkc('.mpbs-profile-setting-box').hide()
      $wkc('.mpbs-profile-setting-overlay').hide()
    })

    // Chat box login form prevent submission
    $wkc('#mpbs-chat-login-form').on('submit', function (event) {
      $wkc('.mpbs-error').remove()
      if ($wkc('#user_login').val() == '' || $wkc('#user_pass').val() == '') {
        event.preventDefault()
        $wkc(this).append('<div class="mpbs-error">Fill all fields!</div>')
      }
    })

    // seller chatbox open/close
    $wkc(document).on('click', '.mpbs-panel-control', function () {
      if (!$wkc(this).hasClass('open')) {
        $wkc(this).addClass('open').parent('.mpbs-chat-menu').addClass('show')
      } else {
        $wkc(this).removeClass('open').parent('.mpbs-chat-menu').removeClass('show')
      }
    })

    if ($wkc('input#buyer-image').length) {
      var fileInput = $wkc('#buyer-image')[0]
      var fileDisplayArea = $wkc('#buyer-profile-image')[0]

      $wkc(fileInput).on('change', function () {
        var file = fileInput.files[0]

        var imageType = /image.*/
        if (file.size > 2000000) {
          $wkc(fileDisplayArea).html('<span class="mpbs-error" style="display:inline-block;width:150px">' + $wkc(this).data('size-error') + '</span>')
        } else {
          if (file.type.match(imageType)) {
            var reader = new FileReader()

            $wkc(reader).on('load', function () {
              fileDisplayArea.innerHTML = ''
              var img = new Image()
              img.src = reader.result
              img.width = 70
              img.height = 60
              $wkc(fileDisplayArea).append(img)
            })
            reader.readAsDataURL(file)
          } else {
            $wkc(fileDisplayArea).html('<span class="mpbs-error" style="display:inline-block;width:150px">' + $wkc(this).data('error') + '</span>')
          }
        }
      })
    }

    var socket
    var socketWorking = false

    // socket status
    if (chatboxCoreConfig.serverRunning) {
      var host = chatboxCoreConfig.host
      socket = io(host)
      socketWorking = true
    }
    console.log(socket)
    setEmoticon()

    var emoticons

    function setEmoticon () {
      var pluginsUrl = chatboxCoreConfig.pluginsPath

      emoticons = {
        ':smiley:': pluginsUrl + 'assets/images/emoji/smiley.png',
        ':smile:': pluginsUrl + 'assets/images/emoji/smile.png',
        ':wink:': pluginsUrl + 'assets/images/emoji/wink.png',
        ':blush:': pluginsUrl + 'assets/images/emoji/blush.png',
        ':angry:': pluginsUrl + 'assets/images/emoji/angry.png',
        ':laughing:': pluginsUrl + 'assets/images/emoji/laughing.png',
        ':smirk:': pluginsUrl + 'assets/images/emoji/smirk.png',
        ':disappointed:': pluginsUrl + 'assets/images/emoji/angry.png',
        ':sleeping:': pluginsUrl + 'assets/images/emoji/sleeping.png',
        ':rage:': pluginsUrl + 'assets/images/emoji/rage.png',
        ':cry:': pluginsUrl + 'assets/images/emoji/cry.png',
        ':yum:': pluginsUrl + 'assets/images/emoji/confused.png',
        ':neutral_face:': pluginsUrl + 'assets/images/emoji/neutral_face.png',
        ':sunglasses:': pluginsUrl + 'assets/images/emoji/sunglasses.png',
        ':astonished:': pluginsUrl + 'assets/images/emoji/astonished.png',
        ':stuck_out_tongue_winking_eye:': pluginsUrl + 'assets/images/emoji/stuck_out_tongue_winking_eye.png',
        ':confused:': pluginsUrl + 'assets/images/emoji/confused.png',
        ':scream:': pluginsUrl + 'assets/images/emoji/scream.png',
        ':stuck_out_tongue:': pluginsUrl + 'assets/images/emoji/stuck_out_tongue.png',
        ':fearful:': pluginsUrl + 'assets/images/emoji/fearful.png',
        ':punch:': pluginsUrl + 'assets/images/emoji/punch.png',
        ':ok_hand:': pluginsUrl + 'assets/images/emoji/ok_hand.png',
        ':clap:': pluginsUrl + 'assets/images/emoji/clap.png',
        ':thumbsup:': pluginsUrl + 'assets/images/emoji/thumbsup.png',
        ':thumbsdown:': pluginsUrl + 'assets/images/emoji/thumbsdown.png'
      }
    }

    function replaceEmoticons(text) {
      if ($wkc.type(text) != 'undefined') {
        return text.replace(/:([_a-zA-Z0-9\+\-]+):/g, function (match) {
          return typeof emoticons[match] != 'undefined' ? "<img alt='" + match + "' src='" + emoticons[match] + "'/>" : match
        })
      } else {
        return false
      }
    }

    var html = $wkc(document).find('.mpbs-emoticons-container').html()
    $wkc(document).find('.mpbs-emoticons-container').html(replaceEmoticons(html))

    $wkc(document).on('click', '.mpbs-emoticons-container .mpbs-smiley-pad img', function () {
      var smileyText = $wkc(this).prop('alt')
      var message = $wkc(this).parents('.mpbs-message-box').children('textarea').val()
      var appendedMessage = message + smileyText + ' '
      $wkc(this).parents('.mpbs-message-box').children('textarea').val(appendedMessage)
      $wkc(this).parents('.mpbs-message-box').children('textarea').focus()
      $wkc(this).parents('.mpbs-emoticons-container').toggle()
    })

    $wkc(document).on('click', '.mpbs-emoticons-button', function () {
      $wkc(this).siblings('.mpbs-emoticons-container').toggle()
    })

    if (enabledCustomerList.customerList) {
      var enabledCustomerChatList = new Array()
      $wkc.each(enabledCustomerList.customerList, function (key, value) {
        var data = $wkc.parseJSON(value.data)
        enabledCustomerChatList.push(data.customerId)
      })
    }

    // seller status change action
    $wkc(document).on('click', '.mpbs-user-select-status.seller .chatStatus', function (evt) {
      var thisElm = $wkc(this)
      var statusCode = $wkc(this).data('id')
      var data = {}
      data.statusCode = statusCode
      data.customers = []

      $wkc.each(enabledCustomerList.customerList, function (i, details) {
        customerData = $wkc.parseJSON(details.data)
        data.customers[i] = customerData.customerId
      })

      if (socketWorking) {
        socket.emit('seller status change', data)
      }
      var sellerId = window.mpbsSellerChatboxConfig.sellerChatData.sellerId
      updateSellerStatus(sellerId, statusCode, thisElm)
    })

    $wkc(document).on('click', '.mpbs-user-select-status.customer .chatStatus', function (evt) {
      var thisElm = $wkc(this)
      var statusCode = $wkc(this).data('id')
      var data = {}
      var customerId = window.mpbsChatboxConfig.customerData.customerId
      data.statusCode = statusCode
      data.customerId = customerId
      data.sellerId = window.mpbsChatboxConfig.sellerData.sellerId
      data.type = 'customer'
      if (socketWorking) {
        socket.emit('customer status change', data)
      }
      updateSellerStatus(customerId, statusCode, thisElm)
    })

    if (typeof window.mpbsSellerChatboxConfig !== 'undefined') {
      setSellerConnected(window.mpbsSellerChatboxConfig.sellerChatData)
    }

    if (typeof window.mpbsSellerChatboxConfig !== 'undefined') {
      var statusCode = window.mpbsSellerChatboxConfig.sellerChatData.chatStatus
      var statusClass = getStatusClass(parseInt(statusCode))
      customersellerChatStatus(statusClass)
    }

    if (typeof window.mpbsChatboxConfig !== 'undefined') {
      var customerData = window.mpbsChatboxConfig.customerData
      var sellerData = window.mpbsChatboxConfig.sellerData
      if (typeof customerData.customerId !== 'undefined' && typeof sellerData.sellerId !== 'undefined') {
        if (customerData.customerId !== sellerData.sellerId) {
          setCustomerConnected(customerData, sellerData)
        }
        loadChatHistoryCustomer(customerData, sellerData, 0)
      }
      var statusCode = window.mpbsChatboxConfig.customerData.chatStatus
      var statusClass = getStatusClass(parseInt(statusCode))
      customersellerChatStatus(statusClass)
    }

    if (typeof window.mpbsChatboxConfig !== 'undefined') {
      var customerStatusCode = window.mpbsChatboxConfig.sellerData.chatStatus
      var customerStatusClass = getStatusClass(parseInt(customerStatusCode))
      sellerChatStatusForCustomer(customerStatusClass)
    }

    function setCustomerConnected (customerData, sellerData) {
      var details = {}
      details.customerData = customerData
      details.sellerId = sellerData.sellerId
      if (socketWorking) {
        socket.emit('newCustomerConneted', details)
      }
    }

    function getStatusClass (status) {
      if (status === 1) {
        return 'online'
      } else if (status === 2) {
        return 'busy'
      } else {
        return 'offline'
      }
    }

    function updateSellerStatus (sellerId, statusCode, thisElm) {
      var statusClass = getStatusClass(parseInt(statusCode))
      $wkc.ajax({
        type: 'post',
        url: chatboxAjax.url,
        data: {
          'action': 'mpbs_update_user_status',
          'nonce': chatboxAjax.nonce,
          'user_id': sellerId,
          'status_code': statusCode
        },
        success: function (response) {
          response = JSON.parse(response)
          if (!response.error) {
            customersellerChatStatus(statusClass)
          } else {
            alert(response.message)
          }
        }
      })
    }

    function customersellerChatStatus (statusClass) {
      $wkc('.mpbs-chatbox-container').find('.mpbs-self-status').children('.status').removeAttr('class').addClass('status').addClass(statusClass)
    }

    function sellerChatStatusForCustomer (statusClass) {
      $wkc('.mpbs-chatbox-container').find('.mpbs_chat_status').children('.status').addClass(statusClass)
    }

    function setSellerConnected (sellerDetails) {
      if (socketWorking) {
        socket.emit('newSellerConneted', sellerDetails)
      }
    }

    // customer start chat
    $wkc(document).on('submit', '#mpbs_start_chat', function (evt) {
      evt.preventDefault()
      var self = this
      var checkData = {}
      var chatData = {}
      var formDataArray = $wkc(this).serializeArray()

      if ($wkc('.mpbs-chatbox-error').length) {
        $wkc('.mpbs-chatbox-error').remove()
      }

      formDataArray.forEach(function (entry) {
        chatData[entry.name] = entry.value
      })

      chatData.dateTime = getDate() + getTime()
      chatData.receiverData = window.mpbsChatboxConfig.sellerData
      chatData.message = chatData.message.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/g, '')

      if (chatData.message == '') {
        return false
      }
      /**
       * before check seller is available or not
       */
      checkData.customerId = chatData.receiverData.sellerId
      checkData.type = 'seller'

      $wkc(self).parent().append('<div class="mpbs-loader" style="display:block"><div class="mpbs-spinner mpbs-skeleton"><!--////--></div></div>')

      checkSellerIsAvailable(checkData).fail(function (response) {
        location.reload()
      }).done(function (response) {
        var responseData = $wkc.parseJSON(response)
        if (responseData.available) {
          initializeChatUserMeta(chatData, self).then(function (response) {
            var customerData = {}
            getCustomerConfiguration(chatData.receiverData.sellerId).done(function (configResponse) {
              configResponse = $wkc.parseJSON(configResponse)
              if (!configResponse.error) {
                customerData = configResponse.message
              }
              window.mpbsChatboxConfig.customerData = customerData
              setCustomerConnected(customerData, chatData.receiverData)
              $wkc(self).parent('.mpbs-start-chat-container').remove()
              var discussionTemplate = wp.template('mpbs_customer_chatbox_template')
              var discussionTemplateData = {}
              discussionTemplateData.receiverId = chatData.receiverData.sellerId
              discussionTemplateData.senderId = customerData.customerId
              discussionTemplateData.customerName = customerData.name
              discussionTemplateData.customerImage = customerData.src
              $wkc('#mpbs-customer-chatbox-' + customerData.customerId).find('.mpbs-chat-controls').show()
              $wkc('#mpbs-customer-chatbox-' + customerData.customerId).append(discussionTemplate(discussionTemplateData))
              $wkc('#mpbs-customer-chatbox-' + customerData.customerId).find('.mpbs-self-status').children('.status').removeAttr('class').addClass('status').addClass('online')

              var emojiHtml = $wkc('#mpbs-customer-chatbox-' + customerData.customerId).find('.mpbs-emoticons-container').html()
              $wkc('#mpbs-customer-chatbox-' + customerData.customerId).find('.mpbs-emoticons-container').html(replaceEmoticons(emojiHtml))

              var input = document.createElement('input')
              input.type = 'hidden'
              input.name = 'receiverId'
              input.value = chatData.receiverData.sellerId
              self.appendChild(input)

              var input = document.createElement('input')
              input.type = 'hidden'
              input.name = 'senderId'
              input.value = customerData.customerId
              self.appendChild(input)

              var input = document.createElement('input')
              input.type = 'hidden'
              input.name = 'customerName'
              input.value = customerData.name
              self.appendChild(input)

              var input = document.createElement('input')
              input.type = 'hidden'
              input.name = 'customerImage'
              input.value = customerData.src
              self.appendChild(input)

              var selfElm = $wkc('#mpbs-form-reply-customer')
              sendCustomerMessage($wkc(self).serializeArray(), selfElm)
            })

            setTimeout(function () {
              $wkc('.mpbs-loader').remove()
            }, 1000)
          })
        } else {
          setTimeout(function () {
            $wkc('.mpbs-loader').remove()
            $wkc(self).before('<p class="mpbs-chatbox-error">' + responseData.message + '</p>')
          }, 1000)
        }
      })
    })

    // get customer configuration
    function getCustomerConfiguration (sellerId) {
      return $wkc.ajax({
        type: 'post',
        url: chatboxAjax.url,
        data: {
          'action': 'mpbs_get_customer_config_in_js',
          'nonce': chatboxAjax.nonce,
          'seller_id': sellerId
        }
      })
    }

    // customer form submit
    $wkc(document).on('keypress', '#mpbs-chatbox-text', function (event) {
      if (event.which == 13 && !event.shiftKey) {
        event.preventDefault()
        $wkc(event.target).parents('form').submit()
      } else if (event.shiftKey && event.keyCode == 13) {
        return true
      } else {
        return true
      }
    })

    // submit customer chat form
    $wkc(document).on('submit', '#mpbs-form-reply-customer', function (event) {
      event.preventDefault()
      var formData = $wkc(this).serializeArray()
      var self = $wkc(this)
      sendCustomerMessage(formData, self)
    })

    // send customer message
    function sendCustomerMessage(formData, self) {
      var data = {}
      var sendData = {}
      var checkData = {}
      formData.forEach(function (entry) {
        sendData[entry.name] = entry.value
      })
      sendData.dateTime = getDate() + getTime()
      sendData.message = sendData.message.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/g, '')
      if ($wkc.trim(sendData.message) !== '') {
        $wkc(self).parents().siblings('.mpbs-thread-container').find('.mpbs-chatbox-error').remove()
        /**
         * before check seller is available or not
         */
        checkData.customerId = sendData.receiverId
        checkData.type = 'seller'

        checkSellerIsAvailable(checkData).fail(function (response) {
          location.reload()
        }).done(function (response) {
          var responseData = $wkc.parseJSON(response)
          if (! responseData.available) {
            setTimeout(function () {
              $wkc(self).parents().siblings('.mpbs-thread-container').append('<p class="mpbs-chatbox-error">' + responseData.message + '</p>')
            }, 1000)
          }
        })

        if (socketWorking) {
          socket.emit('customer send new message', sendData)
        } else {
          $wkc(self).parents().siblings('.mpbs-thread-container').append('<p class="mpbs-chatbox-error">Chat server is not working.</p>')
        }
        saveMessageInDatabase(sendData)

        $wkc(self).trigger('reset')

        data = sendData
        data.class = 'self'
        data.image = data.customerImage
        data.datetime = data.dateTime
        data.message = replaceEmoticons(data.message)
        var replyTemplate = wp.template('mpbs_reply_template_customer')
        $wkc('.mpbs-discussion').append(replyTemplate(data))

        if( $wkc('.mpbs-thread-container') != undefined ) {

          $wkc('.mpbs-thread-container').animate({
            scrollTop: $wkc('.mpbs-thread-container')[0].scrollHeight
          }, 100)
          
        }

      }
    }

    if (socketWorking) {
      socket.on('seller new message received', function (messageData) {
        var data = {}
        data = messageData
        data.class = 'other'
        data.image = data.customerImage
        data.datetime = data.dateTime
        
        data.message =  replaceEmoticons(data.message)
        if (data !== 'undefined') {
          var replyTemplate = wp.template('mpbs_reply_template')
          var href = chatboxAjax.pluginsUrl + '/assets/sound/insight.ogg';
          var audio = new Audio(href);
          audio.play();
          $wkc('#mpbs-chat-window-' + data.senderId + ' .mpbs-discussion').append(replyTemplate(data))
          blinkTab(messageData.message)
          if ($wkc('#mpbs-chat-window-' + data.senderId + ' .mpbs-thread-container').length) {
            $wkc('#mpbs-chat-window-' + data.senderId + ' .mpbs-thread-container').animate({
              scrollTop: $wkc('#mpbs-chat-window-' + data.senderId + ' .mpbs-thread-container')[0].scrollHeight
            }, 100)
          } else {
            $wkc('#mpbs-buyer-' + data.senderId).addClass('mpbs-msg-notify')
          }
        }
      })

      socket.on('customer new message received', function (messageData) {

        var data = {}
        data = messageData
        data.class = 'other'
        data.image = data.sellerImage
        data.datetime = data.dateTime
        var href = chatboxAjax.pluginsUrl + '/assets/sound/insight.ogg';
        data.message = replaceEmoticons(data.message)
        if (data !== 'undefined') {
          if ($wkc('#mpbs-customer-chatbox-' + data.receiverId + ' .mpbs-thread-container').length) {
            var replyTemplate = wp.template('mpbs_reply_template_customer')
            
            var audio = new Audio(href);
            audio.play();
            $wkc('#mpbs-customer-chatbox-' + data.receiverId + ' .mpbs-discussion').append(replyTemplate(data))
            blinkTab(messageData.message)
            $wkc('#mpbs-customer-chatbox-' + data.receiverId + ' .mpbs-thread-container').animate({
              scrollTop: $wkc('#mpbs-customer-chatbox-' + data.receiverId + ' .mpbs-thread-container')[0].scrollHeight
            }, 100)
          }
        }
      })

      // refresh seller chat list
      socket.on('refresh seller chat list', function (data) {
        if ($wkc.inArray(data.customerData.customerId, enabledCustomerChatList) === -1) {
          var sendData = {}
          sendData.data = JSON.stringify(data.customerData)
          enabledCustomerList.customerList.splice(0, 0, sendData)
          enabledCustomerChatList.splice(0, 0, data.customerData.customerId)
          createSellerChatList(enabledCustomerList.customerList)
        }
      })

      // notify seller on customer status change
      socket.on('send customer status change', function (data) {
        statusClass = getStatusClass(parseInt(data.statusCode))
        // customer Status in Seller List
        if ($wkc('#mpbs-buyer-' + data.customerId).length) {
          $wkc('#mpbs-buyer-' + data.customerId).find('.status').removeAttr('class').addClass('status').addClass(statusClass)
        }
        // customer status in chat window
        if ($wkc('#mpbs-chat-window-' + data.customerId).length) {
          $wkc('#mpbs-chat-window-' + data.customerId + ' .mpbs_chat_status').find('.status').removeAttr('class').addClass('status').addClass(statusClass)
        }
      })

      // notify customer on seller status change
      socket.on('send seller status change', function (data) {
        statusClass = getStatusClass(parseInt(data.statusCode))
        // customer Status in Seller List
        if ($wkc('#mpbs-customer-chatbox-' + data.customerId).length) {
          $wkc('#mpbs-customer-chatbox-' + data.customerId + ' .mpbs_chat_status').find('.status').removeAttr('class').addClass('status').addClass(statusClass)
        }
      })
    }

    // customer form submit
    $wkc(document).on('keypress', '.mpbs-chatbox-text-seller', function (event) {
      console.log('s')
      if (event.which == 13 && !event.shiftKey) {
        event.preventDefault()
        $wkc(event.target).parents('form').submit()
      } else if (event.shiftKey && event.keyCode == 13) {
        return true
      } else {
        return true
      }
    })

    // submit seller chat form
    $wkc(document).on('submit', '.mpbs-form-reply-seller', function (event) {
      event.preventDefault()
      var formData = $wkc(this).serializeArray()
      var self = $wkc(this)
      sendSellerMessage(formData, self)
    })
  
    // send seller message to customer
    function sendSellerMessage (formData, self) {
      var data = {}
      var sendData = {}
      var checkData = {}
      formData.forEach(function (entry) {
        sendData[entry.name] = entry.value
      })
      sendData.dateTime = getDate() + getTime()
      sendData.message = sendData.message.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/g, '')
      if ($wkc.trim(sendData.message) !== '') {
        $wkc('#mpbs-chat-window-' + sendData.receiverId + ' .mpbs-thread-container').find('.mpbs-chatbox-error').remove()
        /**
         * before check seller is available or not
         */
        checkData.customerId = sendData.receiverId
        checkData.type = 'customer'
        checkSellerIsAvailable(checkData).fail(function (response) {
          location.reload()
        }).done(function (response) {
          var responseData = $wkc.parseJSON(response)
          if (!responseData.available) {
            setTimeout(function () {
              $wkc('#mpbs-chat-window-' + sendData.receiverId + ' .mpbs-thread-container').append('<p class="mpbs-chatbox-error">' + responseData.message + '</p>')
            }, 1000)
          }
        })

        

        if (socketWorking) {
          socket.emit('seller send new message', sendData)
        } else {
          $wkc('#mpbs-chat-window-' + sendData.receiverId + ' .mpbs-thread-container').append('<p class="mpbs-chatbox-error">Chat server is not working.</p>')
        }

        saveMessageInDatabase(sendData)

        $wkc(self).trigger('reset')

        data = sendData

        data.class = 'self'
        data.image = data.sellerImage
        data.datetime = data.dateTime
        data.message = replaceEmoticons(data.message)
        var replyTemplate = wp.template('mpbs_reply_template')
        $wkc('#mpbs-chat-window-' + sendData.receiverId + ' .mpbs-thread-container .mpbs-discussion').append(replyTemplate(data))

        $wkc('#mpbs-chat-window-' + sendData.receiverId + ' .mpbs-thread-container').animate({
          scrollTop: $wkc('.mpbs-thread-container')[0].scrollHeight
        }, 100)
      }
    }

    // save message in database
    function saveMessageInDatabase (messageData) {
      var data = {}
      data.receiverId = messageData.receiverId
      data.senderId = messageData.senderId
      data.dateTime = messageData.dateTime
      data.message = messageData.message
      return $wkc.ajax({
        type: 'post',
        url: chatboxAjax.url,
        data: {
          'action': 'mpbs_save_chat_data',
          'nonce': chatboxAjax.nonce,
          'data': data
        }
      }).fail(function () {
        location.reload()
      }).done(function (response) {
        response = $wkc.parseJSON(response)
      })
    }

    function getDate () {
      var now = new Date()
      var year = '' + now.getFullYear()
      var month = '' + (now.getMonth() + 1)
      if (month.length == 1) {
        month = '0' + month
      }
      var day = '' + now.getDate()

      if (day.length == 1) {
        day = '0' + day
      }

      return year + '-' + month + '-' + day + ' '
    }

    function getTime () {
      var now = new Date()
      var hour = '' + now.getHours()
      if (hour.length == 1) {
        hour = '0' + hour
      }
      var minute = '' + now.getMinutes()
      if (minute.length == 1) {
        minute = '0' + minute
      }
      var second = '' + now.getSeconds()
      if (second.length == 1) {
        second = '0' + second
      }
      return hour + ':' + minute
    }

    // check Seller Is Available
    function checkSellerIsAvailable (checkData) {
      var returnData = $wkc.ajax({
        type: 'post',
        url: chatboxAjax.url,
        data: {
          'action': 'mpbs_check_seller_is_available',
          'nonce': chatboxAjax.nonce,
          'data': checkData
        }
      })
      return returnData
    }

    // initialize Chat User Meta
    function initializeChatUserMeta (chatData, self) {
      var returnData = $wkc.ajax({
        type: 'post',
        url: chatboxAjax.url,
        data: {
          'action': 'mpbs_initialize_chat_user_meta',
          'nonce': chatboxAjax.nonce,
          'data': chatData
        }
      }).fail(function () {
        location.reload()
      }).done(function (response) {
        var responseData = $wkc.parseJSON(response)
        if (responseData.error) {
          setTimeout(function () {
            $wkc('.mpbs-loader').remove()
            $wkc(self).before('<p class="mpbs-chatbox-error">' + responseData.message + '</p>')
          }, 1000)
        }
      })
      return returnData
    }

    if (enabledCustomerList.customerList) {
      createSellerChatList(enabledCustomerList.customerList)
      function createSellerChatList(enabledCustomerListData) {
        $wkc.each(enabledCustomerListData, function (i, details) {
          customerData = $wkc.parseJSON(details.data)
          if (!$wkc('#mpbs-buyer-' + customerData.customerId).length) {
            var element = customerListItemElement(details)
            $wkc('.mpbs-active-users .mpbs-chat-menu').find('.mpbs-chat-customer-list').prepend(element)
          }
        })
      }
    }

    function customerListItemElement(details) {
      customerData = $wkc.parseJSON(details.data)
      var statusClass = getStatusClass(parseInt(customerData.chatStatus))
      return "<li class='mpbs-chat-current-buyer-list' id='mpbs-buyer-" + customerData.customerId + "' data-customerdata='" + details.data + "'><span class='status " + statusClass + "'></span><span class='mpbs-buyer-image'><img src='" + customerData.src + "' alt='" + customerData.name + "' /></span><span class='mpbs-buyer-name'><strong>" + customerData.name + "</strong><div style='word-wrap: break-word;'><i>" + customerData.email + "</i></div></span></li>"
    }

    // open chat window
    var chatbox = 0
    $wkc(document).on('click', '.mpbs-chat-customer-list li', function () {

      var shift = 0
      var chatTemplate = wp.template('mpbs_customer_chat_window')
      var customerData = $wkc(this).data('customerdata')
      var data = {}
      if(customerData.name.length >1){
      data.chatName = customerData.name
      }else{
        data.chatName = customerData.email
      }
      data.status = getStatusClass(parseInt(customerData.chatStatus))
      data.senderId = window.mpbsSellerChatboxConfig.sellerChatData.sellerId
      data.receiverId = customerData.customerId
      data.sellerImage = window.mpbsSellerChatboxConfig.sellerChatData.image
      $wkc('#mpbs-buyer-' + customerData.customerId).removeClass('mpbs-msg-notify')
      if (!$wkc('#mpbs-chat-window-' + customerData.customerId).length) {
        chatbox++
        if (window.innerWidth < (chatbox * 300)) {
          $wkc('#mpbs-chat-window-container .mpbs-chatbox-container:last-child').remove()
          chatbox--
        }

        $wkc.each($wkc('#mpbs-chat-window-container').children(), function (value) {
          shift += 300
          $wkc(this).css( 'right', shift )
        })
        $wkc('#mpbs-chat-window-container').prepend(chatTemplate(data))
        $wkc('#mpbs-chat-window-' + customerData.customerId).css('overflow', 'hidden')
        $wkc('#mpbs-chat-window-' + customerData.customerId).children('.mpbs-thread-container').append('<div class="mpbs-loader" style="display:block"><div class="mpbs-spinner mpbs-skeleton"><!--////--></div></div>')
        var html = $wkc('#mpbs-chat-window-' + customerData.customerId).find('.mpbs-emoticons-container').html()
        $wkc('#mpbs-chat-window-' + customerData.customerId).find('.mpbs-emoticons-container').html(replaceEmoticons(html))
      }
      loadChatHistorySeller(customerData,loadTime = 1)
    })

    // chat history seller end
    function loadChatHistorySeller (data,loadTime) {
      data.receiverId = window.mpbsSellerChatboxConfig.sellerChatData.sellerId
      data.loadTime = loadTime
      if ($wkc('#mpbs-chat-window-' + data.customerId + ' .mpbs-chatbox-error').length) {
        $wkc('#mpbs-chat-window-' + data.customerId + ' .mpbs-chatbox-error').remove()
      }
      return getChatHistoryData(data).fail(function (response) {
        location.reload()
      }).done(function (response) {
        var responseData = $wkc.parseJSON(response)
        if (!responseData.error) {
          $wkc('#mpbs-chat-window-' + data.customerId + ' .mpbs-discussion').html('')
          $wkc.each(responseData.message, function (key, value) {
            if (value.receiver_id == data.receiverId) {
              value.class = 'other'
              value.image = data.src
            } else {
              value.class = 'self'
              value.image = window.mpbsSellerChatboxConfig.sellerChatData.image
            }
            value.message = replaceEmoticons(value.message)
            var replyTemplate = wp.template('mpbs_reply_template')
            $wkc('#mpbs-chat-window-' + data.customerId + ' .mpbs-discussion').append(replyTemplate(value))
          })
          setTimeout(function () {
            $wkc('#mpbs-chat-window-' + data.customerId + ' .mpbs-loader').remove()
            $wkc('#mpbs-chat-window-' + data.customerId).css('overflow', 'auto')
            $wkc('#mpbs-chat-window-' + data.customerId + ' .mpbs-thread-container').animate({
              scrollTop: $wkc('#mpbs-chat-window-' + data.customerId + ' .mpbs-thread-container')[0].scrollHeight
            }, 10)
          }, 1000)
        } else {
          setTimeout(function () {
            $wkc('#mpbs-chat-window-' + data.customerId + ' .mpbs-loader').remove()
            $wkc('#mpbs-chat-window-' + customerData.customerId).children('.mpbs-thread-container').append('<div class="mpbs-chatbox-error">' + responseData.message + '</div>')
          }, 1000)
        }
      })
    }

   // load chat history seller end manually
   $wkc(document).on('click', '.mpbs-history-options-seller  li', function () {
     
   
    var loadTime = $wkc(this).data('value');
    customerId = $wkc(this).data('customerid');
    var customerDataVal = $wkc("#mpbs-buyer-"+customerId).data('customerdata');
   
    loadChatHistorySeller(customerDataVal, loadTime)
  })



    // load chat history customer end manually
    $wkc(document).on('click', '.mpbs-history-options li', function () {
      var customerData = {}
      var sellerData = {}
      var loadTime = $wkc(this).data('value')
      sellerData = window.mpbsChatboxConfig.sellerData
      customerData = window.mpbsChatboxConfig.customerData
      loadChatHistoryCustomer(customerData, sellerData, loadTime)
    })

    // chat history buyer end
    function loadChatHistoryCustomer (customerData, sellerData, loadtime) {
      var data = {}
      data.customerId = customerData.customerId
      data.receiverId = sellerData.sellerId
      data.loadTime = loadtime
      return getChatHistoryData(data).fail(function (response) {
        location.reload()
      }).done(function (response) {
        var responseData = $wkc.parseJSON(response)
        if (!responseData.error) {
          $wkc('.mpbs-chatbox-container .mpbs-discussion').html('')
          $wkc.each(responseData.message, function (key, value) {
            if (value.receiver_id == data.receiverId) {
              value.class = 'self'
              value.image = customerData.src
            } else {
              value.class = 'other'
              value.image = sellerData.image
            }
            value.message = replaceEmoticons(value.message)
            var replyTemplate = wp.template('mpbs_reply_template_customer')
            $wkc('.mpbs-chatbox-container .mpbs-discussion').append(replyTemplate(value))
          })
          $wkc('#mpbs-customer-chatbox-' + data.customerId + ' .mpbs-thread-container').animate({
            scrollTop: $wkc('#mpbs-customer-chatbox-' + data.customerId + ' .mpbs-thread-container')[0].scrollHeight
          }, 10)
        } else {
          setTimeout(function () {
            $wkc('.mpbs-chatbox-container').children('.mpbs-thread-container').append('<div class="mpbs-chatbox-error">' + responseData.message + '</div>')
          }, 1000)
        }
      })
    }

    // load chat history
    function getChatHistoryData (data) {
      return $wkc.ajax({
        type: 'post',
        url: chatboxAjax.url,
        data: {
          'action': 'mpbs_fetch_chat_history',
          'nonce': chatboxAjax.nonce,
          'data': data
        }
      })
    }

    /**
     * Blink Browser Tab
     */
    function blinkTab (message) {
      var oldTitle = document.title,
        timeoutId,
        blink = function () {
          document.title = document.title == $wkc('<div>').html(message).text() ? ' ' : $wkc('<div>').html(message).text()
        },
        clear = function () {
          clearInterval(timeoutId)
          document.title = oldTitle
          window.onmousemove = null
          timeoutId = null
        }
      if (!timeoutId) {
        timeoutId = setInterval(blink, 1000)
        window.onmousemove = clear
      }
    }
  })
})($wkc)
