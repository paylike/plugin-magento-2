window.PaylikeLogger = {
    date: new Date().getTime(),
    context: {},
    setContext: function(context, $, url) {
      if (!this.enabled()) {
        console.log("logs not enabled, stopping");
        return;
      }

      this.context = context;
      this.$ = $;
      this.url = url;
    },
    log: async function(message, data = {}) {
      if (!this.enabled()) {
        console.log("logs not enabled, stopping");
        return;
      }

      const body = {
        message,
        data,
        date: this.date,
        context: this.context,
      }

      this.$.ajax({
        url: this.url.build("paylike/index/Log"),
        type: 'POST',
        dataType: 'text',
        data: body
      });
    },
    enabled: function () {
      return window.checkoutConfig.config.custom.logsEnabled;
    }
  }
