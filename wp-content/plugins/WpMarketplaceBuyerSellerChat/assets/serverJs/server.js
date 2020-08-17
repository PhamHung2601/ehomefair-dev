var https = require('https');
                var fs = require('fs');

                var options_https = {
                    key: fs.readFileSync('/home/430194.cloudwaysapps.com/nycwmsdmsk/public_html/wp-content/plugins/WpMarketplaceBuyerSellerChat/assets/serverJs/server.key', 'utf8'),
                    cert: fs.readFileSync('/home/430194.cloudwaysapps.com/nycwmsdmsk/public_html/wp-content/plugins/WpMarketplaceBuyerSellerChat/assets/serverJs/server.crt', 'utf8'),
                    requestCert: true,
                    rejectUnauthorized: false
                };

                var app = https.createServer(options_https, function (req, res) {
                    res.setHeader('Access-Control-Allow-Origin', 'ehomefair.com.my');
                    res.writeHead(200, { 'Content-Type': 'text/plain' });
                    res.end('okay')
                });

                var io = require('socket.io')(app)

                var roomUsers = {}

                const PORT = process.env.PORT || 3000

                app.listen(PORT, function () {
                  console.log(PORT)
                })

                io.on('connection', function (socket) {
                  socket.on('newSellerConneted', function (details) {
                    var index = details.sellerId
                    roomUsers[index] = socket.id
                  })

                  socket.on('newCustomerConneted', function (details) {
                    var index = details.customerData.customerId
                    roomUsers[index] = socket.id
                    Object.keys(roomUsers).forEach(function (key, value) {
                      if (key == details.sellerId) {
                        receiverSocketId = roomUsers[key]
                        socket.broadcast.to(receiverSocketId).emit('refresh seller chat list', details)
                      }
                    })
                  })

                  socket.on('customer status change', function (data) {
                    if (typeof (data) !== 'undefined') {
                      Object.keys(roomUsers).forEach(function (key, value) {
                        if (key === data.sellerId) {
                          receiverSocketId = roomUsers[key]
                          socket.broadcast.to(receiverSocketId).emit('send customer status change', data)
                        }
                      })
                    }
                  })

                  socket.on('customer send new message', function (data) {
                    if (typeof (data) !== 'undefined') {
                      Object.keys(roomUsers).forEach(function (key, value) {
                        if (key === data.receiverId) {
                          receiverSocketId = roomUsers[key]
                          socket.broadcast.to(receiverSocketId).emit('seller new message received', data)
                        }
                      })
                    }
                  })

                  socket.on('seller send new message', function (data) {
                    if (typeof (data) !== 'undefined') {
                      Object.keys(roomUsers).forEach(function (key, value) {
                        if (key === data.receiverId) {
                            receiverSocketId = roomUsers[key]
                            socket.broadcast.to(receiverSocketId).emit('customer new message received', data)
                        }
                      })
                    }
                  })

                  socket.on('seller status change', function (data) {
                    if (typeof (data) !== 'undefined') {
                      Object.keys(roomUsers).forEach(function (key, value) {
                        Object(data.customers).forEach(function (k) {
                          if (key == k) {
                            receiverSocketId = roomUsers[key]
                            data.customerId = k
                            socket.broadcast.to(receiverSocketId).emit('send seller status change', data)
                          }
                        })
                      })
                    }
                  })
                })