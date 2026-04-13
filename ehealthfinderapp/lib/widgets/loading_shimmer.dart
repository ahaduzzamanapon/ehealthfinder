import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';

class MedicineShimmer extends StatelessWidget {
  final int count;
  const MedicineShimmer({super.key, this.count = 6});

  @override
  Widget build(BuildContext context) {
    return ListView.separated(
      physics: const NeverScrollableScrollPhysics(),
      shrinkWrap: true,
      itemCount: count,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (_, __) => Shimmer.fromColors(
        baseColor: const Color(0xFFE8EEF4),
        highlightColor: const Color(0xFFF8FAFC),
        child: Container(
          height: 100,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
          ),
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                width: 68, height: 68,
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(height: 14, color: Colors.white, width: double.infinity),
                    const SizedBox(height: 8),
                    Container(height: 12, color: Colors.white, width: 160),
                    const SizedBox(height: 8),
                    Container(height: 12, color: Colors.white, width: 100),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class DoctorShimmer extends StatelessWidget {
  final int count;
  const DoctorShimmer({super.key, this.count = 6});

  @override
  Widget build(BuildContext context) {
    return ListView.separated(
      physics: const NeverScrollableScrollPhysics(),
      shrinkWrap: true,
      itemCount: count,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (_, __) => Shimmer.fromColors(
        baseColor: const Color(0xFFE8EEF4),
        highlightColor: const Color(0xFFF8FAFC),
        child: Container(
          height: 110,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
          ),
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                width: 76, height: 76,
                decoration: const BoxDecoration(
                  color: Colors.white,
                  shape: BoxShape.circle,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(height: 14, color: Colors.white, width: double.infinity),
                    const SizedBox(height: 8),
                    Container(height: 12, color: Colors.white, width: 180),
                    const SizedBox(height: 8),
                    Container(height: 12, color: Colors.white, width: 120),
                    const SizedBox(height: 8),
                    Container(height: 10, color: Colors.white, width: 90),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class GridShimmer extends StatelessWidget {
  const GridShimmer({super.key});

  @override
  Widget build(BuildContext context) {
    return GridView.builder(
      physics: const NeverScrollableScrollPhysics(),
      shrinkWrap: true,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        mainAxisSpacing: 12,
        crossAxisSpacing: 12,
        childAspectRatio: 0.85,
      ),
      itemCount: 6,
      itemBuilder: (_, __) => Shimmer.fromColors(
        baseColor: const Color(0xFFE8EEF4),
        highlightColor: const Color(0xFFF8FAFC),
        child: Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
          ),
        ),
      ),
    );
  }
}
