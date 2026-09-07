import 'package:dio/dio.dart';
class ApiClient {
 ApiClient(String baseUrl):dio=Dio(BaseOptions(baseUrl:baseUrl,headers:{'Accept':'application/json'}));
 final Dio dio;
 void setToken(String token)=>dio.options.headers['Authorization']='Bearer $token';
 Future<Response<T>> get<T>(String path,{Map<String,dynamic>? query})=>dio.get<T>(path,queryParameters:query);
 Future<Response<T>> post<T>(String path,{dynamic data})=>dio.post<T>(path,data:data);
 Future<Response<T>> put<T>(String path,{dynamic data})=>dio.put<T>(path,data:data);
}
