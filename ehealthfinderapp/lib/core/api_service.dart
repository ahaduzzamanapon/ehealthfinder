import 'package:dio/dio.dart';
import 'constants.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  late final Dio _dio = Dio(BaseOptions(
    baseUrl: AppConstants.apiUrl,
    connectTimeout: const Duration(seconds: 15),
    receiveTimeout: const Duration(seconds: 15),
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  ));

  // ── Home ───────────────────────────────────────────────────
  Future<Map<String, dynamic>> home() async {
    final res = await _dio.get('/home');
    return res.data['data'] as Map<String, dynamic>;
  }

  // ── Medicines ───────────────────────────────────────────────
  Future<Map<String, dynamic>> getMedicines({
    String q = '',
    int page = 1,
    int perPage = 20,
  }) async {
    final res = await _dio.get('/medicines', queryParameters: {
      'q': q,
      'page': page,
      'per_page': perPage,
    });
    return res.data;
  }

  Future<Map<String, dynamic>> getMedicine(int id) async {
    final res = await _dio.get('/medicines/$id');
    return res.data['data'];
  }

  // ── Doctors ────────────────────────────────────────────────
  Future<Map<String, dynamic>> getDoctors({
    String q = '',
    int? locationId,
    int? specialtyId,
    int page = 1,
    int perPage = 20,
  }) async {
    final params = <String, dynamic>{
      'q': q,
      'page': page,
      'per_page': perPage,
    };
    if (locationId != null) params['location_id'] = locationId;
    if (specialtyId != null) params['specialty_id'] = specialtyId;
    final res = await _dio.get('/doctors', queryParameters: params);
    return res.data;
  }

  Future<Map<String, dynamic>> getDoctor(int id) async {
    final res = await _dio.get('/doctors/$id');
    return res.data['data'];
  }

  // ── Blog ───────────────────────────────────────────────────
  Future<Map<String, dynamic>> getBlogs({int page = 1, int perPage = 15}) async {
    final res = await _dio.get('/blogs', queryParameters: {'page': page, 'per_page': perPage});
    return res.data;
  }

  Future<Map<String, dynamic>> getBlog(String slug) async {
    final res = await _dio.get('/blogs/$slug');
    return res.data['data'];
  }
}
