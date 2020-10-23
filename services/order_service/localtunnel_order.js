const localtunnel = require('localtunnel');

(async () => {
  const tunnel = await localtunnel({ port: 4300 , subdomain: "eseller" });

  // the assigned public url for your tunnel
  // i.e. https://abcdefgjhij.localtunnel.me
  tunnel.url;
  console.log("API running on :" + tunnel.url)
  console.log("Using port : " + 4300  )

  tunnel.on('close', () => {
    //   console.log("Closing tunnel : " , tunnel.url)
    // tunnels are closed
  });
})();
