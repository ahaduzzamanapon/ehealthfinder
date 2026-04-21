import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../core/constants.dart';
import '../core/api_service.dart';
import '../models/medicine_model.dart';
import '../models/doctor_model.dart';
import '../widgets/loading_shimmer.dart';
import 'medicine/medicine_list_screen.dart';
import 'medicine/medicine_detail_screen.dart';
import 'doctor/doctor_list_screen.dart';
import 'doctor/doctor_detail_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final _api = ApiService();
  Map<String, dynamic>? _homeData;
  bool _loading = true;
  String? _error;
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetch();
  }

  Future<void> _fetch() async {
    try {
      setState(() { _loading = true; _error = null; });
      final data = await _api.home();
      setState(() { _homeData = data; _loading = false; });
    } catch (e) {
      setState(() { _error = e.toString(); _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgLight,
      body: AnnotatedRegion<SystemUiOverlayStyle>(
        value: SystemUiOverlayStyle.light,
        child: CustomScrollView(
          slivers: [
            _buildHeader(),
            SliverToBoxAdapter(
              child: _loading
                  ? _buildLoadingState()
                  : _error != null
                      ? _buildError()
                      : _buildContent(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return SliverAppBar(
      expandedHeight: 200,
      pinned: true,
      backgroundColor: AppColors.primary,
      flexibleSpace: FlexibleSpaceBar(
        background: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Color(0xFF1E1B4B), AppColors.primary, Color(0xFF7C3AED)],
            ),
          ),
          child: SafeArea(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 38, height: 38,
                        decoration: const BoxDecoration(
                          color: Colors.white,
                          shape: BoxShape.circle,
                        ),
                        child: ClipOval(
                          child: Image.asset(
                            'assets/images/icon.png',
                            fit: BoxFit.contain,
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      const Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('eHealthFinder',
                              style: TextStyle(color: Colors.white,
                                  fontWeight: FontWeight.w800, fontSize: 18)),
                          Text('বাংলাদেশ',
                              style: TextStyle(color: Colors.white70, fontSize: 11)),
                        ],
                      ),
                    ],
                  ).animate().fadeIn(duration: 400.ms),
                  const SizedBox(height: 16),
                  const Text('Find Doctors & Medicines',
                      style: TextStyle(
                          color: Colors.white, fontSize: 20,
                          fontWeight: FontWeight.w700))
                      .animate().fadeIn(delay: 100.ms),
                  const SizedBox(height: 4),
                  Text('Search from 50,000+ medicines & doctors',
                      style: TextStyle(color: Colors.white.withOpacity(0.8),
                          fontSize: 13))
                      .animate().fadeIn(delay: 200.ms),
                  const SizedBox(height: 14),
                  // Search bar
                  _SearchBar(controller: _searchCtrl),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildContent() {
    if (_homeData == null) return const SizedBox();
    final stats = _homeData!['stats'] as Map<String, dynamic>? ?? {};

    // Safe list extractor — handles both List and Map (cached Eloquent collection)
    List<dynamic> _safeList(String key) {
      final raw = _homeData![key];
      if (raw == null) return [];
      if (raw is List) return raw;
      if (raw is Map) return raw.values.toList(); // old cache edge case
      return [];
    }

    final meds = _safeList('featured_medicines')
        .map((m) => MedicineModel.fromJson(m as Map<String, dynamic>)).toList();
    final docs = _safeList('featured_doctors')
        .map((d) => DoctorModel.fromJson(d as Map<String, dynamic>)).toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const SizedBox(height: 20),
        _buildStats(stats),
        const SizedBox(height: 24),
        _buildCategories(),
        const SizedBox(height: 28),
        _buildSectionHeader('💊 Featured Medicines', () {
          Navigator.push(context,
              MaterialPageRoute(builder: (_) => const MedicineListScreen()));
        }),
        const SizedBox(height: 12),
        _buildMedicineRow(meds),
        const SizedBox(height: 28),
        _buildSectionHeader('👨‍⚕️ Featured Doctors', () {
          Navigator.push(context,
              MaterialPageRoute(builder: (_) => const DoctorListScreen()));
        }),
        const SizedBox(height: 12),
        _buildDoctorRow(docs),
        const SizedBox(height: 30),
      ],
    );
  }

  Widget _buildStats(Map<String, dynamic> stats) {
    final items = [
      {'label': 'Medicines', 'value': '${stats['medicines'] ?? 0}+', 'icon': Icons.medication, 'color': AppColors.primary},
      {'label': 'Doctors',   'value': '${stats['doctors'] ?? 0}+',   'icon': Icons.person,     'color': AppColors.secondary},
      {'label': 'Cities',    'value': '${stats['locations'] ?? 0}+',  'icon': Icons.location_city, 'color': const Color(0xFFF59E0B)},
      {'label': 'Specialties','value': '${stats['specialties'] ?? 0}+','icon': Icons.local_hospital,'color': const Color(0xFFEF4444)},
    ];

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Wrap(
        spacing: 12,
        runSpacing: 12,
        children: items.asMap().entries.map((e) {
          final item = e.value;
          return AnimatedContainer(
            duration: const Duration(milliseconds: 300),
            width: (MediaQuery.of(context).size.width - 32 - 12) / 2, // 2 items per row
            padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.border),
              boxShadow: [BoxShadow(color: Colors.black.withAlpha(8),
                  blurRadius: 12, offset: const Offset(0, 4))],
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: (item['color'] as Color).withAlpha(25),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(item['icon'] as IconData,
                      color: item['color'] as Color, size: 22),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(item['value'] as String, style: TextStyle(
                        fontWeight: FontWeight.w800, fontSize: 16,
                        color: item['color'] as Color,
                      )),
                      const SizedBox(height: 2),
                      Text(item['label'] as String,
                          style: const TextStyle(color: AppColors.textMed, fontSize: 11.5, fontWeight: FontWeight.w500),
                          maxLines: 1, overflow: TextOverflow.ellipsis),
                    ],
                  ),
                ),
              ],
            ),
          )
              .animate(delay: (e.key * 80).ms)
              .fadeIn(duration: 400.ms)
              .slideY(begin: 0.2);
        }).toList(),
      ),
    );
  }

  Widget _buildCategories() {
    final cats = [
      {'label': 'Medicines', 'icon': Icons.medication_rounded,
       'color': AppColors.primary, 'bg': AppColors.primaryLight},
      {'label': 'Doctors', 'icon': Icons.medical_services_rounded,
       'color': AppColors.secondary, 'bg': const Color(0xFFECFEFF)},
      {'label': 'Emergency', 'icon': Icons.emergency_rounded,
       'color': const Color(0xFFEF4444), 'bg': const Color(0xFFFEF2F2)},
      {'label': 'Blog', 'icon': Icons.article_rounded,
       'color': const Color(0xFFF59E0B), 'bg': const Color(0xFFFFFBEB)},
    ];

    return Container(
      height: 105,
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: ListView(
        scrollDirection: Axis.horizontal,
        clipBehavior: Clip.none,
        children: cats.asMap().entries.map((e) {
          final cat = e.value;
          return GestureDetector(
            onTap: () {
              if (cat['label'] == 'Medicines') {
                Navigator.push(context, MaterialPageRoute(
                    builder: (_) => const MedicineListScreen()));
              } else if (cat['label'] == 'Doctors') {
                Navigator.push(context, MaterialPageRoute(
                    builder: (_) => const DoctorListScreen()));
              }
            },
            child: Container(
              width: 90,
              margin: const EdgeInsets.only(right: 12),
              padding: const EdgeInsets.symmetric(vertical: 16),
              decoration: BoxDecoration(
                color: cat['bg'] as Color,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                    color: (cat['color'] as Color).withAlpha(40), width: 1.5),
                boxShadow: [BoxShadow(color: (cat['color'] as Color).withAlpha(15), 
                    blurRadius: 10, offset: const Offset(0, 4))],
              ),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(cat['icon'] as IconData,
                      color: cat['color'] as Color, size: 28),
                  const SizedBox(height: 8),
                  Text(cat['label'] as String,
                      style: TextStyle(
                        color: cat['color'] as Color,
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                      ),
                      textAlign: TextAlign.center,
                      maxLines: 1),
                ],
              ),
            )
                .animate(delay: (e.key * 70 + 200).ms)
                .scale(duration: 400.ms, curve: Curves.elasticOut,
                    begin: const Offset(0.7, 0.7))
                .fade(duration: 300.ms),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildSectionHeader(String title, VoidCallback onViewAll) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(title, style: const TextStyle(
              fontWeight: FontWeight.w800, fontSize: 16, color: AppColors.textDark)),
          GestureDetector(
            onTap: onViewAll,
            child: const Text('View All', style: TextStyle(
                color: AppColors.primary, fontSize: 13,
                fontWeight: FontWeight.w600)),
          ),
        ],
      ),
    );
  }

  Widget _buildMedicineRow(List<MedicineModel> meds) {
    return SizedBox(
      height: 220,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: meds.length,
        separatorBuilder: (_, __) => const SizedBox(width: 12),
        itemBuilder: (ctx, i) {
          final m = meds[i];
          return GestureDetector(
            onTap: () => Navigator.push(ctx, MaterialPageRoute(
                builder: (_) => MedicineDetailScreen(medicineId: m.id))),
            child: Container(
              width: 140,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.border),
                boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.03),
                    blurRadius: 8)],
              ),
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: m.imageUrl != null
                        ? CachedNetworkImage(
                            imageUrl: m.imageUrl!,
                            height: 70, width: 110, fit: BoxFit.contain,
                            errorWidget: (_, __, ___) => const Icon(
                                Icons.medication_rounded,
                                size: 40, color: AppColors.primary),
                          )
                        : const Icon(Icons.medication_rounded,
                            size: 40, color: AppColors.primary),
                  ),
                  const SizedBox(height: 8),
                  Text(m.name, style: const TextStyle(
                      fontWeight: FontWeight.w700, fontSize: 12.5,
                      color: AppColors.textDark),
                      maxLines: 1, overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 3),
                  Text(m.dosageForm ?? '', style: const TextStyle(
                      color: AppColors.textLight, fontSize: 10.5),
                      maxLines: 1),
                  const Spacer(),
                  Text(m.price ?? '', style: const TextStyle(
                      color: AppColors.primary, fontWeight: FontWeight.w700,
                      fontSize: 12)),
                ],
              ),
            )
                .animate(delay: (i * 60).ms)
                .fadeIn(duration: 350.ms)
                .slideX(begin: 0.1),
          );
        },
      ),
    );
  }

  Widget _buildDoctorRow(List<DoctorModel> docs) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        children: docs.take(4).toList().asMap().entries.map((e) {
          final d = e.value;
          return GestureDetector(
            onTap: () => Navigator.push(context,
                MaterialPageRoute(builder: (_) => DoctorDetailScreen(doctorId: d.id))),
            child: Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.border),
                boxShadow: [BoxShadow(color: Colors.black.withAlpha(5), blurRadius: 10, offset: const Offset(0, 4))],
              ),
              child: Row(
                children: [
                  _buildDocAvatar(d),
                  const SizedBox(width: 14),
                  Expanded(child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(d.name, style: const TextStyle(
                          fontWeight: FontWeight.w800, fontSize: 14.5, color: AppColors.textDark),
                          maxLines: 1, overflow: TextOverflow.ellipsis),
                      const SizedBox(height: 3),
                      if (d.specialty != null)
                        Text(d.specialty!, style: const TextStyle(
                            color: AppColors.secondary, fontSize: 12.5, fontWeight: FontWeight.w600),
                            maxLines: 1, overflow: TextOverflow.ellipsis),
                      const SizedBox(height: 2),
                      if (d.location != null)
                        Text('📍 ${d.location!}', style: const TextStyle(
                            color: AppColors.textLight, fontSize: 11),
                            maxLines: 1, overflow: TextOverflow.ellipsis),
                    ],
                  )),
                  const Icon(Icons.arrow_forward_ios_rounded,
                      size: 14, color: AppColors.textLight),
                ],
              ),
            )
                .animate(delay: (e.key * 70 + 100).ms)
                .fadeIn(duration: 350.ms)
                .slideX(begin: 0.05),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildDocAvatar(DoctorModel d) {
    const size = 52.0;
    if (d.imageUrl != null) {
      return ClipOval(
        child: CachedNetworkImage(
          imageUrl: d.imageUrl!, width: size, height: size, fit: BoxFit.cover,
          errorWidget: (_, __, ___) => _docInitial(d, size),
        ),
      );
    }
    return _docInitial(d, size);
  }

  Widget _docInitial(DoctorModel d, double size) {
    return Container(
      width: size, height: size,
      decoration: const BoxDecoration(
        color: AppColors.secondary, shape: BoxShape.circle,
      ),
      child: Center(
        child: Text(
          d.name.isNotEmpty ? d.name[0] : 'D',
          style: const TextStyle(color: Colors.white, fontSize: 20,
              fontWeight: FontWeight.bold),
        ),
      ),
    );
  }

  Widget _buildLoadingState() {
    return const Padding(
      padding: EdgeInsets.all(16),
      child: Column(
        children: [
          SizedBox(height: 20),
          GridShimmer(),
          SizedBox(height: 20),
          MedicineShimmer(count: 4),
        ],
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Column(
          children: [
            const SizedBox(height: 40),
            const Icon(Icons.wifi_off_rounded, size: 60, color: AppColors.textLight),
            const SizedBox(height: 16),
            const Text('Unable to connect',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700,
                    color: AppColors.textDark)),
            const SizedBox(height: 8),
            const Text('Please check your internet connection',
                style: TextStyle(color: AppColors.textMed),
                textAlign: TextAlign.center),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _fetch,
              icon: const Icon(Icons.refresh),
              label: const Text('Retry'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SearchBar extends StatelessWidget {
  final TextEditingController controller;
  const _SearchBar({required this.controller});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        Navigator.push(context,
            MaterialPageRoute(builder: (_) => const MedicineListScreen(autoFocus: true)));
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.08),
              blurRadius: 16, offset: const Offset(0, 4))],
        ),
        child: const Row(
          children: [
            Icon(Icons.search_rounded, color: AppColors.primary, size: 20),
            SizedBox(width: 10),
            Text('Search medicines, doctors...',
                style: TextStyle(color: AppColors.textLight, fontSize: 14)),
          ],
        ),
      )
          .animate(delay: 300.ms)
          .fadeIn(duration: 400.ms)
          .slideY(begin: 0.2),
    );
  }
}
