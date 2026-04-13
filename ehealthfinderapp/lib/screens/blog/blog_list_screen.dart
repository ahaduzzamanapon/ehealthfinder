import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../../core/constants.dart';
import '../../core/api_service.dart';
import '../../models/blog_model.dart';
import 'blog_detail_screen.dart';

class BlogListScreen extends StatefulWidget {
  const BlogListScreen({super.key});

  @override
  State<BlogListScreen> createState() => _BlogListScreenState();
}

class _BlogListScreenState extends State<BlogListScreen> {
  final _api = ApiService();
  final _scrollCtrl = ScrollController();

  List<BlogModel> _items = [];
  bool _loading = true;
  bool _loadingMore = false;
  String? _error;
  int _page = 1;
  int _lastPage = 1;

  @override
  void initState() {
    super.initState();
    _fetch();
    _scrollCtrl.addListener(() {
      if (_scrollCtrl.position.pixels >= _scrollCtrl.position.maxScrollExtent - 200
          && !_loadingMore && _page < _lastPage) _fetchMore();
    });
  }

  Future<void> _fetch() async {
    setState(() { _loading = true; _error = null; _page = 1; _items = []; });
    try {
      final res = await _api.getBlogs(page: _page);
      final data = (res['data'] as List).map((b) => BlogModel.fromJson(b)).toList();
      final meta = res['meta'] as Map<String, dynamic>;
      setState(() { _items = data; _lastPage = meta['last_page'] ?? 1; _loading = false; });
    } catch (e) {
      setState(() { _error = e.toString(); _loading = false; });
    }
  }

  Future<void> _fetchMore() async {
    setState(() { _loadingMore = true; _page++; });
    try {
      final res = await _api.getBlogs(page: _page);
      final data = (res['data'] as List).map((b) => BlogModel.fromJson(b)).toList();
      setState(() { _items = [..._items, ...data]; _loadingMore = false; });
    } catch (_) {
      setState(() { _page--; _loadingMore = false; });
    }
  }

  @override
  void dispose() { _scrollCtrl.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgLight,
      appBar: AppBar(
        backgroundColor: const Color(0xFFF59E0B),
        foregroundColor: Colors.white,
        title: const Text('Health Blog', style: TextStyle(fontWeight: FontWeight.w800)),
        elevation: 0,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFFF59E0B)))
          : _error != null
              ? _buildError()
              : _items.isEmpty
                  ? const Center(child: Text('No blog posts yet'))
                  : ListView.builder(
                      controller: _scrollCtrl,
                      padding: const EdgeInsets.all(16),
                      itemCount: _items.length + (_loadingMore ? 1 : 0),
                      itemBuilder: (ctx, i) {
                        if (i == _items.length) {
                          return const Padding(padding: EdgeInsets.all(16),
                              child: Center(child: CircularProgressIndicator(
                                  color: Color(0xFFF59E0B))));
                        }
                        return _buildBlogCard(_items[i], i, ctx);
                      },
                    ),
    );
  }

  Widget _buildBlogCard(BlogModel b, int i, BuildContext ctx) {
    return GestureDetector(
      onTap: () => Navigator.push(ctx, MaterialPageRoute(
          builder: (_) => BlogDetailScreen(slug: b.slug))),
      child: Container(
        margin: const EdgeInsets.only(bottom: 14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.border),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.03),
              blurRadius: 10, offset: const Offset(0, 4))],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (b.thumbnail != null)
              ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                child: CachedNetworkImage(
                  imageUrl: b.thumbnail!,
                  height: 160, width: double.infinity, fit: BoxFit.cover,
                  errorWidget: (_, __, ___) => Container(
                    height: 120, color: const Color(0xFFFFFBEB),
                    child: const Center(child: Icon(Icons.article_rounded,
                        size: 48, color: Color(0xFFF59E0B))),
                  ),
                ),
              ),
            Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (b.category != null)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFFBEB),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(b.category!, style: const TextStyle(
                          color: Color(0xFFF59E0B), fontSize: 11,
                          fontWeight: FontWeight.w700)),
                    ),
                  const SizedBox(height: 8),
                  Text(b.title, style: const TextStyle(
                      fontWeight: FontWeight.w800, fontSize: 15,
                      color: AppColors.textDark),
                      maxLines: 2, overflow: TextOverflow.ellipsis),
                  if (b.excerpt != null) ...[
                    const SizedBox(height: 6),
                    Text(b.excerpt!, style: const TextStyle(
                        color: AppColors.textMed, fontSize: 13, height: 1.5),
                        maxLines: 2, overflow: TextOverflow.ellipsis),
                  ],
                  if (b.publishedAt != null) ...[
                    const SizedBox(height: 8),
                    Text(b.publishedAt!, style: const TextStyle(
                        color: AppColors.textLight, fontSize: 11)),
                  ],
                ],
              ),
            ),
          ],
        ),
      )
          .animate(delay: (i * 60).ms)
          .fadeIn(duration: 350.ms)
          .slideY(begin: 0.08),
    );
  }

  Widget _buildError() => Center(child: Column(
    mainAxisAlignment: MainAxisAlignment.center,
    children: [
      const Icon(Icons.error_outline, size: 48, color: AppColors.textLight),
      const SizedBox(height: 12),
      const Text('Error loading blogs'),
      const SizedBox(height: 16),
      ElevatedButton(onPressed: _fetch,
          style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFF59E0B),
              foregroundColor: Colors.white),
          child: const Text('Retry')),
    ],
  ));
}
