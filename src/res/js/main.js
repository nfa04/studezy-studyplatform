navigator.serviceWorker.register("/sw.js");

navigator.serviceWorker.ready.then((serviceWorkerRegistration) => {
    const options = {
      userVisibleOnly: true,
      applicationServerKey: 'BHJwee-KAwDWYIRO7XreaAf-dldPVunEx-Z8LKEFgL1QKwxH_iYCADDMWY4BhPqsb6DE2OlCVn9vh9r9fwoHnrw'
    };
    serviceWorkerRegistration.pushManager.subscribe(options).then(
      (pushSubscription) => {
        console.log(pushSubscription.endpoint);
        var api = new APIRequest("push_subscribe").sendAndTrigger({
            data: JSON.stringify(pushSubscription)
        });
      },
      (error) => {
        console.error(error);
      }
    );
  });