import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../core/constants.dart';
import '../../core/api_service.dart';
import '../../models/blog_model.dart';

class BlogDetailScreen extends StatefulWidget {
  final String slug;
  const BlogDetailScreen({super.key, required this.slug});

  @override
  State<BlogDetailScreen> createState() => _BlogDetailScreenState();
}

class _BlogDetailScreenState extends State<BlogDetailScreen> {
  final _api = ApiService();
  BlogModel? _blog;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _fetch();
  }

  Future<void> _fetch() async {
    try {
      final data = await _api.getBlog(widget.slug);
      setState(() { _blog = BlogModel.fromJson(data); _loading = false; });
    } catch (e) {
      setState(() { _error = e.toString(); _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgLight,
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: AppColors.textDark,
        elevation: 0.5,
        title: const Text('Blog', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFFF59E0B)))
          : _error != null
              ? Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                  const Icon(Icons.error_outline, size: 48, color: AppColors.textLight),
                  const SizedBox(height: 12),
                  const Text('Failed to load blog'),
                  const SizedBox(height: 16),
                  ElevatedButton(onPressed: _fetch,
                      style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFFF59E0B),
                          foregroundColor: Colors.white),
                      child: const Text('Retry')),
                ]))
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (_blog!.thumbnail != null)
                        ClipRRect(
                          borderRadius: BorderRadius.circular(14),
                          child: CachedNetworkImage(
                            imageUrl: _blog!.thumbnail!,
                            width: double.infinity, height: 200, fit: BoxFit.cover,
                          ),
                        ),
                      const SizedBox(height: 16),
                      if (_blog!.category != null)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFFFBEB),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(_blog!.category!, style: const TextStyle(
                              color: Color(0xFFF59E0B), fontWeight: FontWeight.w700,
                              fontSize: 12)),
                        ),
                      const SizedBox(height: 10),
                      Text(_blog!.title, style: const TextStyle(
                          fontSize: 22, fontWeight: FontWeight.w800,
                          color: AppColors.textDark, height: 1.3)),
                      if (_blog!.publishedAt != null) ...[
                        const SizedBox(height: 6),
                        Text(_blog!.publishedAt!, style: const TextStyle(
                            color: AppColors.textLight, fontSize: 12)),
                      ],
                      const SizedBox(height: 16),
                      const Divider(),
                      const SizedBox(height: 16),
                      if (_blog!.content != null)
                        Text(
                          _blog!.content!.replaceAll(RegExp(r'<[^>]*>'), '').trim(),
                          style: const TextStyle(
                              color: AppColors.textMed, fontSize: 15, height: 1.8),
                        ),
                      const SizedBox(height: 30),
                    ],
                  ),
                ),
    );
  }
}
