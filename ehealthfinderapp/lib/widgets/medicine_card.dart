import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../core/constants.dart';
import '../models/medicine_model.dart';

class MedicineCard extends StatelessWidget {
  final MedicineModel medicine;
  final VoidCallback onTap;
  final int index;

  const MedicineCard({
    super.key,
    required this.medicine,
    required this.onTap,
    this.index = 0,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 5),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.border, width: 1),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Material(
          color: Colors.transparent,
          borderRadius: BorderRadius.circular(16),
          child: InkWell(
            borderRadius: BorderRadius.circular(16),
            onTap: onTap,
            splashColor: AppColors.primaryLight,
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  // Image / Icon
                  _buildImage(),
                  const SizedBox(width: 14),
                  // Text Info
                  Expanded(child: _buildInfo()),
                  // Price Chip
                  _buildPriceChip(),
                ],
              ),
            ),
          ),
        ),
      ),
    )
        .animate(delay: (index * 50).ms)
        .fadeIn(duration: 350.ms)
        .slideX(begin: 0.05, duration: 350.ms, curve: Curves.easeOut);
  }

  Widget _buildImage() {
    if (medicine.imageUrl != null) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(10),
        child: CachedNetworkImage(
          imageUrl: medicine.imageUrl!,
          width: 64, height: 64,
          fit: BoxFit.contain,
          placeholder: (_, __) => Container(
            width: 64, height: 64,
            color: AppColors.bgLight,
            child: const Icon(Icons.medication, color: AppColors.primary, size: 32),
          ),
          errorWidget: (_, __, ___) => _medIcon(),
        ),
      );
    }
    return _medIcon();
  }

  Widget _medIcon() {
    return Container(
      width: 64, height: 64,
      decoration: BoxDecoration(
        color: AppColors.primaryLight,
        borderRadius: BorderRadius.circular(10),
      ),
      child: const Icon(Icons.medication_rounded, color: AppColors.primary, size: 32),
    );
  }

  Widget _buildInfo() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Flexible(
              child: Text(
                medicine.name,
                style: const TextStyle(
                  fontWeight: FontWeight.w700,
                  fontSize: 15,
                  color: AppColors.textDark,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
            if (medicine.isAntibiotic) ...[
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF2F2),
                  borderRadius: BorderRadius.circular(4),
                  border: Border.all(color: const Color(0xFFFECACA)),
                ),
                child: const Text('AB', style: TextStyle(
                  fontSize: 9, color: AppColors.danger, fontWeight: FontWeight.w700,
                )),
              ),
            ],
          ],
        ),
        const SizedBox(height: 4),
        if (medicine.dosageForm != null)
          Text(
            medicine.dosageForm!,
            style: const TextStyle(color: AppColors.textMed, fontSize: 12.5),
          ),
        const SizedBox(height: 4),
        if (medicine.company != null)
          Text(
            medicine.company!,
            style: const TextStyle(color: AppColors.textLight, fontSize: 11.5),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        if (medicine.generic != null)
          Container(
            margin: const EdgeInsets.only(top: 5),
            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(4),
            ),
            child: Text(
              medicine.generic!,
              style: const TextStyle(color: AppColors.secondary, fontSize: 10.5, fontWeight: FontWeight.w600),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
      ],
    );
  }

  Widget _buildPriceChip() {
    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      crossAxisAlignment: CrossAxisAlignment.end,
      children: [
        const Icon(Icons.arrow_forward_ios_rounded,
            size: 13, color: AppColors.textLight),
        const SizedBox(height: 6),
        if (medicine.price != null)
          Text(
            medicine.price!,
            style: const TextStyle(
              color: AppColors.primary,
              fontWeight: FontWeight.w700,
              fontSize: 12.5,
            ),
          ),
      ],
    );
  }
}
