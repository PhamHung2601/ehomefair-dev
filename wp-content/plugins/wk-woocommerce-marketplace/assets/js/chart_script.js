const wkmpChart = jQuery.noConflict();

wkmpChart(document).ready(function () {
  wkmpChart('#mp-update-sale-order').on('change', function (evt) {
    evt.preventDefault()
    wkmpChart(window).scrollTop(0)
    wkmpChart('body').append('<div class="wk-mp-loader"><div class="wk-mp-spinner wk-mp-skeleton"><!--////--></div></div>')
    wkmpChart('.wk-mp-loader').css('display', 'inline-block')
    wkmpChart('body').css('overflow', 'hidden')
    setTimeout(function () {
      wkmpChart('body').css('overflow', 'auto')
      wkmpChart('.wk-mp-loader').remove()
    }, 1500)
  })

});


google.load("visualization", "1", { packages: ["geochart"] });

google.setOnLoadCallback(function () { googleChartLoadCb() });
