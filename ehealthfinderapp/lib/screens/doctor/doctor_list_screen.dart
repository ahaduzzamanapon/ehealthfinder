import 'package:flutter/material.dart';
import '../../core/constants.dart';
import '../../core/api_service.dart';
import '../../models/doctor_model.dart';
import '../../widgets/doctor_card.dart';
import '../../widgets/loading_shimmer.dart';
import 'doctor_detail_screen.dart';

class DoctorListScreen extends StatefulWidget {
  final int? preselectedLocationId;
  final int? preselectedSpecialtyId;
  const DoctorListScreen({super.key, this.preselectedLocationId, this.preselectedSpecialtyId});

  @override
  State<DoctorListScreen> createState() => _DoctorListScreenState();
}

class _DoctorListScreenState extends State<DoctorListScreen> {
  final _api = ApiService();
  final _searchCtrl = TextEditingController();
  final _scrollCtrl = ScrollController();

  List<DoctorModel> _items = [];
  bool _loading = true;
  bool _loadingMore = false;
  String? _error;
  int _page = 1;
  int _lastPage = 1;
  String _query = '';
  int? _locationId;
  int? _specialtyId;

  @override
  void initState() {
    super.initState();
    _locationId  = widget.preselectedLocationId;
    _specialtyId = widget.preselectedSpecialtyId;
    _fetch();
    _scrollCtrl.addListener(() {
      if (_scrollCtrl.position.pixels >= _scrollCtrl.position.maxScrollExtent - 200
          && !_loadingMore && _page < _lastPage) {
        _fetchMore();
      }
    });
  }

  Future<void> _fetch({bool reset = true}) async {
    if (reset) setState(() { _loading = true; _error = null; _page = 1; _items = []; });
    try {
      final res = await _api.getDoctors(
          q: _query, locationId: _locationId, specialtyId: _specialtyId, page: _page);
      final data = (res['data'] as List).map((d) => DoctorModel.fromJson(d)).toList();
      final meta = res['meta'] as Map<String, dynamic>;
      setState(() {
        _items = reset ? data : [..._items, ...data];
        _lastPage = meta['last_page'] ?? 1;
        _loading = false;
      });
    } catch (e) {
      setState(() { _error = e.toString(); _loading = false; });
    }
  }

  Future<void> _fetchMore() async {
    setState(() { _loadingMore = true; _page++; });
    try {
      final res = await _api.getDoctors(
          q: _query, locationId: _locationId, specialtyId: _specialtyId, page: _page);
      final data = (res['data'] as List).map((d) => DoctorModel.fromJson(d)).toList();
      setState(() { _items = [..._items, ...data]; _loadingMore = false; });
    } catch (_) {
      setState(() { _page--; _loadingMore = false; });
    }
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    _scrollCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgLight,
      appBar: AppBar(
        backgroundColor: AppColors.secondary,
        foregroundColor: Colors.white,
        elevation: 0,
        title: TextField(
          controller: _searchCtrl,
          onChanged: (v) { if (v.length == 0 || v.length >= 2) { _query = v; _fetch(); } },
          style: const TextStyle(color: Colors.white, fontSize: 15),
          decoration: InputDecoration(
            hintText: 'Search doctors...',
            hintStyle: TextStyle(color: Colors.white.withOpacity(0.7)),
            border: InputBorder.none,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.tune_rounded),
            onPressed: _showFilters,
          ),
        ],
      ),
      body: _loading
          ? const Padding(padding: EdgeInsets.all(16), child: DoctorShimmer(count: 7))
          : _error != null
              ? _buildError()
              : _items.isEmpty
                  ? _buildEmpty()
                  : ListView.builder(
                      controller: _scrollCtrl,
                      padding: const EdgeInsets.all(16),
                      itemCount: _items.length + (_loadingMore ? 1 : 0),
                      itemBuilder: (ctx, i) {
                        if (i == _items.length) {
                          return const Padding(
                            padding: EdgeInsets.all(16),
                            child: Center(child: CircularProgressIndicator(
                                color: AppColors.secondary)),
                          );
                        }
                        final d = _items[i];
                        return DoctorCard(
                          doctor: d, index: i,
                          onTap: () => Navigator.push(ctx, MaterialPageRoute(
                              builder: (_) => DoctorDetailScreen(doctorId: d.id))),
                        );
                      },
                    ),
    );
  }

  void _showFilters() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => Container(
        padding: const EdgeInsets.all(24),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: const Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('Filter Doctors', style: TextStyle(
                fontWeight: FontWeight.w800, fontSize: 18)),
            SizedBox(height: 20),
            Text('Filters coming soon...', style: TextStyle(color: AppColors.textMed)),
            SizedBox(height: 30),
          ],
        ),
      ),
    );
  }

  Widget _buildError() => Center(child: Column(
    mainAxisAlignment: MainAxisAlignment.center,
    children: [
      const Icon(Icons.error_outline, size: 48, color: AppColors.textLight),
      const SizedBox(height: 12),
      const Text('Error loading doctors'),
      const SizedBox(height: 16),
      ElevatedButton(onPressed: () => _fetch(),
          style: ElevatedButton.styleFrom(backgroundColor: AppColors.secondary,
              foregroundColor: Colors.white),
          child: const Text('Retry')),
    ],
  ));

  Widget _buildEmpty() => Center(child: Column(
    mainAxisAlignment: MainAxisAlignment.center,
    children: [
      const Icon(Icons.person_search_rounded, size: 60, color: AppColors.textLight),
      const SizedBox(height: 12),
      Text(_query.isEmpty ? 'No doctors found' : 'No results for "$_query"',
          style: const TextStyle(color: AppColors.textMed, fontSize: 15)),
    ],
  ));
}
