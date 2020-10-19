const localtunnel = require('localtunnel');

(async () => {
  const tunnel = await localtunnel({ port: 5700 , subdomain: "eseller" });

  // the assigned public url for your tunnel
  // i.e. https://abcdefgjhij.localtunnel.me
  tunnel.url;
  console.log("API running on :" + tunnel.url)
  console.log("Using port : " + 5700  )

  tunnel.on('close', () => {
    //   console.log("Closing tunnel : " , tunnel.url)
    // tunnels are closed
  });
})();
