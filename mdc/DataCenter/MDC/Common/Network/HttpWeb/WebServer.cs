using System.IO;
using System.Net;
using System.Text;

namespace MDC.Common.Network.HttpWeb
{
    public class WebServer
    {
        public bool IsListen { get; set; } = false;

        private readonly string address;

        private readonly int port;

        private readonly HttpListener httpListener = new HttpListener();

        public WebServer(string address, int port)
        {
            this.address = address;
            this.port = port;
        }

        public void Listen()
        {
            IsListen = true;

            RestoreListener();

            while(IsListen)
            {
                HttpListenerContext context = httpListener.GetContext();

                AddHeaders(context.Response);

                if (context.Request.HttpMethod == "POST" || context.Request.HttpMethod == "GET")
                {
                    HandleRequest(context.Request, context.Response);
                }

                context.Response.Close();
            }

            httpListener.Stop();
        }

        private void RestoreListener()
        {
            foreach(string currentAddress in address.Split(';'))
            {
                httpListener.Prefixes.Add($"http://{currentAddress}:{port}/");
            }
            
            httpListener.Start();
        }

        private void HandleRequest(HttpListenerRequest request, HttpListenerResponse response)
        {
            string data = new StreamReader(request.InputStream, request.ContentEncoding).ReadToEnd();

            Context.Current = CreateRequestInfo(request);

            ExecutionResult executionResult = Router.Execute(Context.Current, data, request.Url.LocalPath.Substring(1));
            SendResponse(response, executionResult, request.ContentEncoding);

            General.Log($"{request.HttpMethod} request -> {request.Url.LocalPath}");
        }

        private void AddHeaders(HttpListenerResponse response)
        {
            response.AddHeader("Access-Control-Allow-Headers", "Content-Type, Accept, Cache-Control, Authorization");
            response.AddHeader("Access-Control-Allow-Methods", "GET, POST");
            response.AppendHeader("Access-Control-Allow-Origin", "*");
        }

        private RequestInfo CreateRequestInfo(HttpListenerRequest request)
        {
            return new RequestInfo
            {
                Address = request.RemoteEndPoint.ToString(),
                AccessToken = request.Headers.Get("Authorization")
            };
        }

        private void SendResponse(HttpListenerResponse response, ExecutionResult executionResult, Encoding encoding)
        {
            response.StatusCode = executionResult.StatusCode;

            if (executionResult.JsonText == null)
            {
                return;
            }

            byte[] responseBytes = encoding.GetBytes(executionResult.JsonText);
            response.OutputStream.Write(responseBytes, 0, responseBytes.Length);
        }
    }
}
