import 'package:flutter/material.dart';
import '../../core/constants.dart';
import '../../core/api_service.dart';
import '../../models/medicine_model.dart';
import '../../widgets/medicine_card.dart';
import '../../widgets/loading_shimmer.dart';
import 'medicine_detail_screen.dart';

class MedicineListScreen extends StatefulWidget {
  final bool autoFocus;
  const MedicineListScreen({super.key, this.autoFocus = false});

  @override
  State<MedicineListScreen> createState() => _MedicineListScreenState();
}

class _MedicineListScreenState extends State<MedicineListScreen> {
  final _api = ApiService();
  final _searchCtrl = TextEditingController();
  final _scrollCtrl = ScrollController();
  final _focusNode = FocusNode();

  List<MedicineModel> _items = [];
  bool _loading = true;
  bool _loadingMore = false;
  String? _error;
  int _page = 1;
  int _lastPage = 1;
  String _query = '';

  @override
  void initState() {
    super.initState();
    _fetch();
    _scrollCtrl.addListener(_onScroll);
    if (widget.autoFocus) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _focusNode.requestFocus();
      });
    }
  }

  void _onScroll() {
    if (_scrollCtrl.position.pixels >= _scrollCtrl.position.maxScrollExtent - 200
        && !_loadingMore && _page < _lastPage) {
      _fetchMore();
    }
  }

  Future<void> _fetch({bool reset = true}) async {
    if (reset) {
      setState(() { _loading = true; _error = null; _page = 1; _items = []; });
    }
    try {
      final res = await _api.getMedicines(q: _query, page: _page);
      final data = (res['data'] as List).map((m) => MedicineModel.fromJson(m)).toList();
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
      final res = await _api.getMedicines(q: _query, page: _page);
      final data = (res['data'] as List).map((m) => MedicineModel.fromJson(m)).toList();
      setState(() { _items = [..._items, ...data]; _loadingMore = false; });
    } catch (_) {
      setState(() { _page--; _loadingMore = false; });
    }
  }

  void _onSearch(String q) {
    _query = q;
    _fetch();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    _scrollCtrl.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgLight,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        elevation: 0,
        title: TextField(
          controller: _searchCtrl,
          focusNode: _focusNode,
          onChanged: (v) {
            if (v.isEmpty || v.length >= 2) _onSearch(v);
          },
          style: const TextStyle(color: Colors.white, fontSize: 15),
          decoration: InputDecoration(
            hintText: 'Search medicines...',
            hintStyle: TextStyle(color: Colors.white.withOpacity(0.7)),
            border: InputBorder.none,
            suffixIcon: _searchCtrl.text.isNotEmpty
                ? IconButton(
                    icon: const Icon(Icons.close, color: Colors.white70, size: 18),
                    onPressed: () {
                      _searchCtrl.clear();
                      _onSearch('');
                    },
                  )
                : null,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.search_rounded, color: Colors.white),
            onPressed: () => _onSearch(_searchCtrl.text),
          ),
        ],
      ),
      body: _loading
          ? const Padding(
              padding: EdgeInsets.all(16),
              child: MedicineShimmer(count: 8),
            )
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
                                color: AppColors.primary)),
                          );
                        }
                        final m = _items[i];
                        return MedicineCard(
                          medicine: m,
                          index: i,
                          onTap: () => Navigator.push(ctx, MaterialPageRoute(
                              builder: (_) => MedicineDetailScreen(medicineId: m.id))),
                        );
                      },
                    ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, size: 48, color: AppColors.textLight),
          const SizedBox(height: 12),
          const Text('Error loading medicines',
              style: TextStyle(fontWeight: FontWeight.w600)),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: () => _fetch(),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary,
                foregroundColor: Colors.white),
            child: const Text('Retry'),
          ),
        ],
      ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.medication_rounded, size: 60, color: AppColors.textLight),
          const SizedBox(height: 12),
          Text(_query.isEmpty ? 'No medicines found' : 'No results for "$_query"',
              style: const TextStyle(color: AppColors.textMed, fontSize: 15)),
        ],
      ),
    );
  }
}
