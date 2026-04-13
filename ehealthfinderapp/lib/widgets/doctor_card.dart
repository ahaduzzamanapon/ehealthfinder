import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../core/constants.dart';
import '../models/doctor_model.dart';

class DoctorCard extends StatelessWidget {
  final DoctorModel doctor;
  final VoidCallback onTap;
  final int index;

  const DoctorCard({
    super.key,
    required this.doctor,
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
          border: Border.all(color: AppColors.border),
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
            splashColor: const Color(0xFFEFF6FF),
            onTap: onTap,
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildAvatar(),
                  const SizedBox(width: 14),
                  Expanded(child: _buildInfo()),
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

  Widget _buildAvatar() {
    const size = 72.0;
    if (doctor.imageUrl != null) {
      return ClipOval(
        child: CachedNetworkImage(
          imageUrl: doctor.imageUrl!,
          width: size, height: size,
          fit: BoxFit.cover,
          placeholder: (_, __) => _defaultAvatar(size),
          errorWidget: (_, __, ___) => _defaultAvatar(size),
        ),
      );
    }
    return _defaultAvatar(size);
  }

  Widget _defaultAvatar(double size) {
    return Container(
      width: size, height: size,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [Color(0xFF2563EB), Color(0xFF1D4ED8)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        shape: BoxShape.circle,
      ),
      child: Center(
        child: Text(
          doctor.name.isNotEmpty ? doctor.name[0].toUpperCase() : 'D',
          style: const TextStyle(
            color: Colors.white, fontSize: 26,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }

  Widget _buildInfo() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          doctor.name,
          style: const TextStyle(
            fontWeight: FontWeight.w700,
            fontSize: 15,
            color: AppColors.textDark,
          ),
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
        if (doctor.degrees != null) ...[
          const SizedBox(height: 3),
          Text(
            doctor.degrees!,
            style: const TextStyle(color: AppColors.textMed, fontSize: 12),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
        const SizedBox(height: 6),
        if (doctor.specialty != null)
          _tag(Icons.local_hospital_rounded, doctor.specialty!, AppColors.secondary,
              const Color(0xFFEFF6FF)),
        if (doctor.location != null) ...[
          const SizedBox(height: 5),
          _tag(Icons.location_on_rounded, doctor.location!, AppColors.primary,
              AppColors.primaryLight),
        ],
        if (doctor.hospital != null) ...[
          const SizedBox(height: 5),
          Text(
            doctor.hospital!,
            style: const TextStyle(color: AppColors.textLight, fontSize: 11),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
        if (doctor.appointmentNumber != null) ...[
          const SizedBox(height: 8),
          Row(
            children: [
              const Icon(Icons.phone_rounded,
                  size: 12, color: AppColors.success),
              const SizedBox(width: 4),
              Text(
                doctor.appointmentNumber!,
                style: const TextStyle(
                  color: AppColors.success,
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
        ],
      ],
    );
  }

  Widget _tag(IconData icon, String text, Color color, Color bgColor) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 12, color: color),
        const SizedBox(width: 4),
        Flexible(
          child: Text(
            text,
            style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.w600),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }
}
