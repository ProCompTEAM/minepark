using System.IO;
using System.Net;
using System.Text;
using System.Threading.Tasks;

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

        public async Task Listen()
        {
            IsListen = true;

            RestoreListener();

            while(IsListen)
            {
                Task<HttpListenerContext> context = httpListener.GetContextAsync();

                AddHeaders(context.Result.Response);

                if (context.Result.Request.HttpMethod == "POST" || context.Result.Request.HttpMethod == "GET")
                {
                    await HandleRequest(context.Result.Request, context.Result.Response);
                }

                context.Result.Response.Close();
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

        private async Task HandleRequest(HttpListenerRequest request, HttpListenerResponse response)
        {
            string data = new StreamReader(request.InputStream, request.ContentEncoding).ReadToEnd();

            Context.Current = CreateRequestInfo(request);

            ExecutionResult executionResult = Router.Execute(Context.Current, data, request.Url.LocalPath[1..]);
            await SendResponse(response, executionResult, request.ContentEncoding);

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

        private async Task SendResponse(HttpListenerResponse response, ExecutionResult executionResult, Encoding encoding)
        {
            response.StatusCode = executionResult.StatusCode;

            if (executionResult.JsonText == null)
            {
                return;
            }

            byte[] responseBytes = encoding.GetBytes(executionResult.JsonText);
            await response.OutputStream.WriteAsync(responseBytes, 0, responseBytes.Length);
        }
    }
}
