using MDC.Common.Network.HttpWeb.Json;
using MDC.Infrastructure;
using MDC.Infrastructure.Controllers.Interfaces;
using MDC.Infrastructure.Providers;
using MDC.Infrastructure.Providers.Interfaces;

using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Reflection;
using System.Text.Json;
using System.Threading.Tasks;

namespace MDC.Common.Network.HttpWeb
{
    public static class Router
    {
        private static readonly JsonSerializerOptions jsonSerializeOptions = GetJsonSerializeOptions();

        private static readonly JsonSerializerOptions jsonDeserializeOptions = GetJsonDeserializeOptions();

        private static readonly IUnitProvider unitProvider;

        static Router()
        {
            unitProvider = Store.GetProvider<UnitProvider>();
        }

        public static ExecutionResult Execute(RequestContext requestContext, string jsonData, string target)
        {
            string[] routes = target.Split('/');

            if(routes.Length < 2)
            {
                return CreateExecutionResult(HttpStatusCode.BadRequest);
            }

            if(!unitProvider.Authorize(requestContext.AccessToken))
            {
                General.Error("Declined request from ", requestContext.Address);
                General.Error("Invalid access token = {0}", requestContext.AccessToken);
                return CreateExecutionResult(HttpStatusCode.Forbidden);
            }

            try
            {
                IController controller = Store.GetControllerByRoute(routes[0]);
                string result = ExecuteMethod(controller, routes[1], requestContext, jsonData);
                return CreateExecutionResult(HttpStatusCode.OK, result);
            }
            catch(Exception exception)
            {
                General.Crash(exception.Message, exception.StackTrace.Split("\n\r"));
                return CreateExecutionResult(HttpStatusCode.InternalServerError);
            }
        }

        private static ExecutionResult CreateExecutionResult(HttpStatusCode statusCode, string jsonResponse = null)
        {
            return new ExecutionResult
            {
                StatusCode = (int)statusCode,
                JsonText = jsonResponse
            };
        }

        private static string ExecuteMethod(IController controller, string methodName, RequestContext requestContext, string jsonData)
        {
            methodName = NormalizeMethodName(methodName);
            Type currentType = controller.GetType();
            MethodInfo method = currentType.GetMethod(methodName);

            if(!IsMethodContainArgument(method, typeof(RequestContext)))
            {
                requestContext = null;
            }

            object data = null;

            if(!JsonDataIsNullOrEmpty(jsonData))
            {
                data = JsonSerializer.Deserialize(jsonData, GetMethodArgumentType(method), jsonDeserializeOptions);
            }

            object result = method.Invoke(controller, PrepareArguments(data, requestContext));
            return GetSerializationResult(result);
        }

        private static string GetSerializationResult(object invokeResult)
        {
            if (invokeResult is Task)
            {
                Task task = (Task) invokeResult;

                var result = task.GetType().GetProperty("Result").GetValue(task);
                return result == null ? null : JsonSerializer.Serialize(result, jsonSerializeOptions);
            }

            return JsonSerializer.Serialize(invokeResult, jsonSerializeOptions);
        }

        private static bool IsMethodContainArgument(MethodInfo method, Type argumentType)
        {
            return method.GetParameters().Count(pi => pi.ParameterType == argumentType) == 1;
        }

        private static Type GetMethodArgumentType(MethodInfo method, int argumentIndex = 0)
        {
            return method.GetParameters()[argumentIndex].ParameterType;
        }

        private static bool JsonDataIsNullOrEmpty(string jsonData)
        {
            return jsonData == "null" || jsonData == null || jsonData.Trim() == string.Empty;
        }

        private static object[] PrepareArguments(object data, RequestContext requestInfo)
        {
            List<object> args = new List<object>();

            if(data != null)
            {
                args.Add(data);
            }

            if (requestInfo != null)
            {
                args.Add(requestInfo);
            }

            return args.ToArray();
        }

        private static string NormalizeMethodName(string methodName)
        {
            string result = methodName.Substring(0, 1).ToUpper();

            for(int index = 1; index < methodName.Length; index++)
            {
                if(methodName[index] == '-')
                {
                    result += methodName.Substring(++index, 1).ToUpper();
                    continue;
                }
                
                result += methodName[index];
            }

            return result;
        }

        private static JsonSerializerOptions GetJsonSerializeOptions()
        {
            JsonSerializerOptions options = new JsonSerializerOptions
            {
                PropertyNamingPolicy = new SerializeNamingPolicy()
            };
            options.Converters.Add(new DateTimeConverter());

            return options;
        }

        private static JsonSerializerOptions GetJsonDeserializeOptions()
        {
            JsonSerializerOptions options = new JsonSerializerOptions
            {
                PropertyNameCaseInsensitive = true
            };
            options.Converters.Add(new DateTimeConverter());

            return options;
        }
    }
}