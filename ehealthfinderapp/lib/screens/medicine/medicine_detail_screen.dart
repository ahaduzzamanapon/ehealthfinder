import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../core/constants.dart';
import '../../core/api_service.dart';
import '../../models/medicine_model.dart';

class MedicineDetailScreen extends StatefulWidget {
  final int medicineId;
  const MedicineDetailScreen({super.key, required this.medicineId});

  @override
  State<MedicineDetailScreen> createState() => _MedicineDetailScreenState();
}

class _MedicineDetailScreenState extends State<MedicineDetailScreen> {
  final _api = ApiService();
  MedicineModel? _medicine;
  bool _loading = true;
  String? _error;
  final Set<String> _expanded = {};
  bool _isBangla = false; // Language toggle

  @override
  void initState() {
    super.initState();
    _fetch();
  }

  Future<void> _fetch() async {
    try {
      final data = await _api.getMedicine(widget.medicineId);
      setState(() {
        _medicine = MedicineModel.fromJson(data);
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgLight,
      body: _loading
          ? const Scaffold(
              body: Center(
                  child: CircularProgressIndicator(color: AppColors.primary)),
            )
          : _error != null
              ? _buildError()
              : _buildDetail(),
    );
  }

  Widget _buildDetail() {
    final m = _medicine!;
    return CustomScrollView(
      slivers: [
        SliverAppBar(
          expandedHeight: 240,
          pinned: true,
          backgroundColor: AppColors.primary,
          foregroundColor: Colors.white,
          actions: [
            // EN / BN Language Toggle
            Padding(
              padding: const EdgeInsets.only(right: 12),
              child: GestureDetector(
                onTap: () => setState(() => _isBangla = !_isBangla),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 250),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'EN',
                        style: TextStyle(
                          fontWeight: FontWeight.w800,
                          fontSize: 12,
                          color: !_isBangla
                              ? AppColors.primary
                              : AppColors.textLight,
                        ),
                      ),
                      const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 4),
                        child: Text('|',
                            style: TextStyle(
                                color: AppColors.textLight, fontSize: 12)),
                      ),
                      Text(
                        'বাং',
                        style: GoogleFonts.hindSiliguri(
                          fontWeight: FontWeight.w800,
                          fontSize: 12,
                          color: _isBangla
                              ? AppColors.primary
                              : AppColors.textLight,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
          flexibleSpace: FlexibleSpaceBar(
            background: Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [Color(0xFF065F46), AppColors.primary],
                ),
              ),
              child: SafeArea(
                child: Center(
                  child: Padding(
                    padding: const EdgeInsets.only(top: 40),
                    child: m.imageUrl != null
                        ? CachedNetworkImage(
                            imageUrl: m.imageUrl!,
                            height: 150,
                            fit: BoxFit.contain,
                            errorWidget: (_, __, ___) => const Icon(
                                Icons.medication_rounded,
                                size: 80,
                                color: Colors.white54),
                          )
                        : const Icon(Icons.medication_rounded,
                            size: 90, color: Colors.white54),
                  ),
                ),
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
                _buildHeader(m),
                const SizedBox(height: 16),
                _buildInfoGrid(m),
                const SizedBox(height: 20),
                if (m.sections != null) ..._buildSectionsList(m.sections!),
                if (m.alternatives.isNotEmpty) ...[
                  const SizedBox(height: 20),
                  _buildAlternatives(m.alternatives),
                ],
                const SizedBox(height: 30),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildHeader(MedicineModel m) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    m.name,
                    style: GoogleFonts.poppins(
                      fontSize: 24,
                      fontWeight: FontWeight.w800,
                      color: AppColors.textDark,
                    ),
                  ),
                  // Show Bangla name if available and Bangla mode
                  if (_isBangla && m.banglaName != null)
                    Text(
                      m.banglaName!,
                      style: GoogleFonts.hindSiliguri(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: AppColors.primary,
                      ),
                    ),
                ],
              ),
            ),
            if (m.isAntibiotic)
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF2F2),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: const Color(0xFFFECACA)),
                ),
                child: const Text('⚠️ Antibiotic',
                    style: TextStyle(
                        color: AppColors.danger,
                        fontSize: 11,
                        fontWeight: FontWeight.w700)),
              ),
          ],
        ),
        if (m.dosageForm != null) ...[
          const SizedBox(height: 4),
          Text(m.dosageForm!,
              style: const TextStyle(color: AppColors.textMed, fontSize: 15)),
        ],
        if (m.generic != null) ...[
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text('Generic: ${m.generic!}',
                style: const TextStyle(
                    color: AppColors.secondary,
                    fontSize: 12.5,
                    fontWeight: FontWeight.w600)),
          ),
        ],
      ],
    ).animate().fadeIn(duration: 400.ms).slideY(begin: 0.1);
  }

  Widget _buildInfoGrid(MedicineModel m) {
    final items = [
      {
        'label': 'Manufacturer',
        'value': m.company ?? '—',
        'icon': Icons.business_rounded,
        'color': AppColors.secondary
      },
      {
        'label': 'Strength',
        'value': m.strength ?? '—',
        'icon': Icons.science_rounded,
        'color': AppColors.primary
      },
      {
        'label': 'Unit Price',
        'value': m.price ?? '—',
        'icon': Icons.payments_rounded,
        'color': const Color(0xFFF59E0B)
      },
      {
        'label': 'Rating',
        'value': '${m.rating} ★ (${m.reviewCount})',
        'icon': Icons.star_rounded,
        'color': const Color(0xFFF59E0B)
      },
    ];

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        children: items.asMap().entries.map((e) {
          final item = e.value;
          return Padding(
            padding:
                EdgeInsets.only(bottom: e.key < items.length - 1 ? 12 : 0),
            child: Row(
              children: [
                Container(
                  width: 36,
                  height: 36,
                  decoration: BoxDecoration(
                    color: (item['color'] as Color).withAlpha(25),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(item['icon'] as IconData,
                      color: item['color'] as Color, size: 18),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(item['label'] as String,
                          style: const TextStyle(
                              color: AppColors.textLight, fontSize: 11)),
                      Text(item['value'] as String,
                          style: const TextStyle(
                              fontWeight: FontWeight.w600,
                              fontSize: 13.5,
                              color: AppColors.textDark)),
                    ],
                  ),
                ),
              ],
            ),
          );
        }).toList(),
      ),
    ).animate(delay: 100.ms).fadeIn(duration: 400.ms);
  }

  List<Widget> _buildSectionsList(Map<String, dynamic> sections) {
    final labels = {
      'indications': {'en': '📋 Indications', 'bn': '📋 নির্দেশনা'},
      'pharmacology': {'en': '🧬 Pharmacology', 'bn': '🧬 ফার্মাকোলজি'},
      'dosage': {'en': '💊 Dosage & Administration', 'bn': '💊 মাত্রা ও সেবনবিধি'},
      'interaction': {'en': '⚡ Drug Interactions', 'bn': '⚡ ওষুধ মিথস্ক্রিয়া'},
      'contraindications': {
        'en': '🚫 Contraindications',
        'bn': '🚫 প্রতিনির্দেশনা'
      },
      'side_effects': {'en': '⚠️ Side Effects', 'bn': '⚠️ পার্শ্বপ্রতিক্রিয়া'},
      'pregnancy': {
        'en': '🤰 Pregnancy & Lactation',
        'bn': '🤰 গর্ভাবস্থা ও স্তন্যপান'
      },
      'precautions': {'en': '🛡️ Precautions', 'bn': '🛡️ সতর্কতা'},
      'pediatric': {'en': '👶 Pediatric Usage', 'bn': '👶 শিশুদের ব্যবহার'},
      'storage': {'en': '📦 Storage Conditions', 'bn': '📦 সংরক্ষণ পদ্ধতি'},
    };

    final widgets = <Widget>[];
    sections.forEach((key, value) {
      final map = value as Map<String, dynamic>?;
      final enContent = map?['en'] as String? ?? '';
      final bnContent = map?['bn'] as String? ?? '';
      final content = _isBangla && bnContent.trim().isNotEmpty
          ? bnContent
          : enContent;
      if (content.trim().isEmpty) return;

      final labelMap = labels[key] ?? {'en': key, 'bn': key};
      final label = _isBangla ? labelMap['bn']! : labelMap['en']!;
      final isExpanded = _expanded.contains(key);

      // Check if both languages have content → show toggle chip
      final hasBoth =
          enContent.trim().isNotEmpty && bnContent.trim().isNotEmpty;

      widgets.add(
        Container(
          margin: const EdgeInsets.only(bottom: 10),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.border),
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(14),
            child: ExpansionTile(
              key: Key('$key-$_isBangla'),
              initiallyExpanded: isExpanded,
              onExpansionChanged: (v) {
                setState(() {
                  if (v) {
                    _expanded.add(key);
                  } else {
                    _expanded.remove(key);
                  }
                });
              },
              tilePadding:
                  const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              title: Text(label,
                  style: _isBangla
                      ? GoogleFonts.hindSiliguri(
                          fontWeight: FontWeight.w700,
                          fontSize: 14,
                          color: AppColors.textDark)
                      : const TextStyle(
                          fontWeight: FontWeight.w700,
                          fontSize: 14,
                          color: AppColors.textDark)),
              iconColor: AppColors.primary,
              collapsedIconColor: AppColors.textLight,
              children: [
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                  child: Text(
                    _stripHtml(content),
                    style: _isBangla
                        ? GoogleFonts.hindSiliguri(
                            color: AppColors.textMed,
                            fontSize: 14,
                            height: 1.8)
                        : const TextStyle(
                            color: AppColors.textMed,
                            fontSize: 13.5,
                            height: 1.7),
                  ),
                ),
              ],
            ),
          ),
        ).animate().fadeIn(duration: 300.ms),
      );
    });
    return widgets;
  }

  Widget _buildAlternatives(List<MedicineModel> alts) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('💊 Alternative Medicines',
            style: TextStyle(
                fontWeight: FontWeight.w800,
                fontSize: 16,
                color: AppColors.textDark)),
        const SizedBox(height: 12),
        ...alts.map((a) => GestureDetector(
              onTap: () => Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(
                      builder: (_) =>
                          MedicineDetailScreen(medicineId: a.id))),
              child: Container(
                margin: const EdgeInsets.only(bottom: 8),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.border),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.medication_rounded,
                        color: AppColors.primary, size: 20),
                    const SizedBox(width: 10),
                    Expanded(
                        child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(a.name,
                            style: const TextStyle(
                                fontWeight: FontWeight.w700)),
                        Text(a.company ?? '',
                            style: const TextStyle(
                                color: AppColors.textMed, fontSize: 12)),
                      ],
                    )),
                    Text(a.price ?? '',
                        style: const TextStyle(
                            color: AppColors.primary,
                            fontWeight: FontWeight.w700)),
                    const SizedBox(width: 6),
                    const Icon(Icons.arrow_forward_ios_rounded,
                        size: 12, color: AppColors.textLight),
                  ],
                ),
              ),
            )),
      ],
    );
  }

  String _stripHtml(String html) {
    return html.replaceAll(RegExp(r'<[^>]*>'), '').trim();
  }

  Widget _buildError() {
    return Scaffold(
      appBar: AppBar(
          backgroundColor: AppColors.primary,
          foregroundColor: Colors.white,
          title: const Text('Medicine Detail')),
      body: Center(
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          const Icon(Icons.error_outline, size: 48, color: AppColors.textLight),
          const SizedBox(height: 12),
          const Text('Failed to load medicine'),
          const SizedBox(height: 16),
          ElevatedButton(
              onPressed: _fetch,
              style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white),
              child: const Text('Retry')),
        ]),
      ),
    );
  }
}
