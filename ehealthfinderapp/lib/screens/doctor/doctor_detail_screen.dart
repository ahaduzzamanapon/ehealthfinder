import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants.dart';
import '../../core/api_service.dart';
import '../../models/doctor_model.dart';

class DoctorDetailScreen extends StatefulWidget {
  final int doctorId;
  const DoctorDetailScreen({super.key, required this.doctorId});

  @override
  State<DoctorDetailScreen> createState() => _DoctorDetailScreenState();
}

class _DoctorDetailScreenState extends State<DoctorDetailScreen> {
  final _api = ApiService();
  DoctorModel? _doctor;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _fetch();
  }

  Future<void> _fetch() async {
    try {
      final data = await _api.getDoctor(widget.doctorId);
      setState(() { _doctor = DoctorModel.fromJson(data); _loading = false; });
    } catch (e) {
      setState(() { _error = e.toString(); _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgLight,
      body: _loading
          ? const Scaffold(
              body: Center(child: CircularProgressIndicator(color: AppColors.secondary)),
            )
          : _error != null
              ? _buildError()
              : _buildDetail(),
    );
  }

  Widget _buildDetail() {
    final d = _doctor!;
    return CustomScrollView(
      slivers: [
        SliverAppBar(
          expandedHeight: 260,
          pinned: true,
          backgroundColor: AppColors.secondary,
          foregroundColor: Colors.white,
          flexibleSpace: FlexibleSpaceBar(
            background: Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topLeft, end: Alignment.bottomRight,
                  colors: [Color(0xFF1E3A8A), AppColors.secondary, Color(0xFF3B82F6)],
                ),
              ),
              child: SafeArea(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const SizedBox(height: 40),
                    // Avatar
                    _buildAvatar(d, 100),
                    const SizedBox(height: 14),
                    Text(d.name, style: const TextStyle(
                        color: Colors.white, fontSize: 20,
                        fontWeight: FontWeight.w800)),
                    if (d.degrees != null)
                      Text(d.degrees!, style: TextStyle(
                          color: Colors.white.withOpacity(0.8), fontSize: 13)),
                  ],
                ).animate().fadeIn(duration: 500.ms),
              ),
            ),
          ),
        ),

        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Info cards
                _buildInfoRow(d),
                const SizedBox(height: 20),
                // About
                if (d.about != null && d.about!.isNotEmpty) ...[
                  _buildCard('About', Icons.info_outline_rounded, d.about!),
                  const SizedBox(height: 16),
                ],
                // Chambers
                const Text('📅 Chambers & Appointments',
                    style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16,
                        color: AppColors.textDark)),
                const SizedBox(height: 12),
                ...d.chambers.asMap().entries.map((e) =>
                    _buildChamberCard(e.value, e.key)),
                const SizedBox(height: 30),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildAvatar(DoctorModel d, double size) {
    if (d.imageUrl != null) {
      return ClipOval(
        child: CachedNetworkImage(
          imageUrl: d.imageUrl!, width: size, height: size, fit: BoxFit.cover,
          errorWidget: (_, __, ___) => _initAvatar(d, size),
        ),
      );
    }
    return _initAvatar(d, size);
  }

  Widget _initAvatar(DoctorModel d, double size) {
    return Container(
      width: size, height: size,
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.2),
        shape: BoxShape.circle,
        border: Border.all(color: Colors.white, width: 3),
      ),
      child: Center(
        child: Text(d.name.isNotEmpty ? d.name[0] : 'D',
            style: TextStyle(color: Colors.white, fontSize: size * 0.38,
                fontWeight: FontWeight.bold)),
      ),
    );
  }

  Widget _buildInfoRow(DoctorModel d) {
    final chips = <Map<String, dynamic>>[
      if (d.specialty != null)
        {'label': d.specialty!, 'icon': Icons.local_hospital_rounded,
         'color': AppColors.secondary, 'bg': const Color(0xFFEFF6FF)},
      if (d.location != null)
        {'label': d.location!, 'icon': Icons.location_on_rounded,
         'color': AppColors.primary, 'bg': AppColors.primaryLight},
      if (d.experience != null)
        {'label': d.experience!, 'icon': Icons.timer_rounded,
         'color': const Color(0xFFF59E0B), 'bg': const Color(0xFFFFFBEB)},
    ];

    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: chips.asMap().entries.map((e) {
        final c = e.value;
        return Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
          decoration: BoxDecoration(
            color: c['bg'] as Color,
            borderRadius: BorderRadius.circular(50),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(c['icon'] as IconData, size: 14, color: c['color'] as Color),
              const SizedBox(width: 5),
              Text(c['label'] as String,
                  style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600,
                      color: c['color'] as Color)),
            ],
          ),
        )
            .animate(delay: (e.key * 80).ms)
            .fadeIn(duration: 350.ms)
            .scale(begin: const Offset(0.8, 0.8));
      }).toList(),
    );
  }

  Widget _buildCard(String title, IconData icon, String text) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            Icon(icon, size: 16, color: AppColors.secondary),
            const SizedBox(width: 6),
            Text(title, style: const TextStyle(fontWeight: FontWeight.w700,
                color: AppColors.textDark)),
          ]),
          const SizedBox(height: 10),
          Text(text, style: const TextStyle(color: AppColors.textMed,
              height: 1.6, fontSize: 13.5)),
        ],
      ),
    );
  }

  Widget _buildChamberCard(ChamberModel c, int index) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.03),
            blurRadius: 10, offset: const Offset(0, 4))],
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.local_hospital_rounded,
                    color: AppColors.secondary, size: 18),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(c.hospital, style: const TextStyle(
                      fontWeight: FontWeight.w800, fontSize: 15,
                      color: AppColors.textDark)),
                ),
              ],
            ),
            if (c.hospitalLocation != null) ...[
              const SizedBox(height: 4),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(c.hospitalLocation!, style: const TextStyle(
                    color: AppColors.secondary, fontSize: 11.5,
                    fontWeight: FontWeight.w600)),
              ),
            ],
            const SizedBox(height: 14),
            if (c.address != null)
              _chamberRow(Icons.location_on_rounded, c.address!, AppColors.primary),
            if (c.visitingHour != null) ...[
              const SizedBox(height: 8),
              _chamberRow(Icons.access_time_rounded, c.visitingHour!,
                  const Color(0xFFF59E0B)),
            ],
            if (c.appointmentNumber != null) ...[
              const SizedBox(height: 14),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () => launchUrl(
                      Uri.parse('tel:${c.appointmentNumber!.replaceAll(RegExp(r'[^0-9+]'), '')}')),
                  icon: const Icon(Icons.phone_rounded, size: 16),
                  label: Text('Call: ${c.appointmentNumber!}'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.success,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    )
        .animate(delay: (index * 100).ms)
        .fadeIn(duration: 400.ms)
        .slideY(begin: 0.1);
  }

  Widget _chamberRow(IconData icon, String text, Color color) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 15, color: color),
        const SizedBox(width: 8),
        Expanded(child: Text(text, style: const TextStyle(
            color: AppColors.textMed, fontSize: 13.5, height: 1.5))),
      ],
    );
  }

  Widget _buildError() {
    return Scaffold(
      appBar: AppBar(backgroundColor: AppColors.secondary, foregroundColor: Colors.white,
          title: const Text('Doctor Detail')),
      body: Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
        const Icon(Icons.error_outline, size: 48, color: AppColors.textLight),
        const SizedBox(height: 12),
        const Text('Failed to load doctor'),
        const SizedBox(height: 16),
        ElevatedButton(onPressed: _fetch,
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.secondary,
                foregroundColor: Colors.white),
            child: const Text('Retry')),
      ])),
    );
  }
}
