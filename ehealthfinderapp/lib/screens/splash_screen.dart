import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../core/constants.dart';
import '../main.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with TickerProviderStateMixin {
  late AnimationController _pulseCtrl;

  @override
  void initState() {
    super.initState();
    _pulseCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1500),
    )..repeat(reverse: true);

    Future.delayed(const Duration(milliseconds: 2800), () {
      if (mounted) {
        Navigator.of(context).pushReplacement(
          PageRouteBuilder(
            pageBuilder: (_, __, ___) => const MainShell(),
            transitionsBuilder: (_, anim, __, child) =>
                FadeTransition(opacity: anim, child: child),
            transitionDuration: const Duration(milliseconds: 500),
          ),
        );
      }
    });
  }

  @override
  void dispose() {
    _pulseCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              Color(0xFF0B2A1F),
              Color(0xFF0EA47A),
              Color(0xFF0B5E47),
            ],
          ),
        ),
        child: Stack(
          children: [
            // Decorative circles
            Positioned(
              top: -60, right: -60,
              child: _circle(200, AppColors.primary.withOpacity(0.15)),
            ),
            Positioned(
              bottom: -80, left: -80,
              child: _circle(260, AppColors.primary.withOpacity(0.10)),
            ),
            Positioned(
              top: 120, left: -40,
              child: _circle(120, Colors.white.withOpacity(0.05)),
            ),

            // Main content
            Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Logo icon with animated rings
                  AnimatedBuilder(
                    animation: _pulseCtrl,
                    builder: (ctx, child) {
                      return Stack(
                        alignment: Alignment.center,
                        children: [
                          // Outer pulse ring
                          Container(
                            width: 140 + 20 * _pulseCtrl.value,
                            height: 140 + 20 * _pulseCtrl.value,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              border: Border.all(
                                color: Colors.white
                                    .withOpacity(0.15 - 0.1 * _pulseCtrl.value),
                                width: 2,
                              ),
                            ),
                          ),
                          // Inner pulse ring
                          Container(
                            width: 110 + 10 * _pulseCtrl.value,
                            height: 110 + 10 * _pulseCtrl.value,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              border: Border.all(
                                color: Colors.white
                                    .withOpacity(0.2 - 0.1 * _pulseCtrl.value),
                                width: 1.5,
                              ),
                            ),
                          ),
                          child!,
                        ],
                      );
                    },
                    child: Container(
                      width: 96,
                      height: 96,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        shape: BoxShape.circle,
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.25),
                            blurRadius: 30,
                            spreadRadius: 5,
                          ),
                        ],
                      ),
                      child: const Center(
                        child: Icon(
                          Icons.local_hospital_rounded,
                          color: AppColors.primary,
                          size: 52,
                        ),
                      ),
                    ),
                  )
                      .animate()
                      .scale(
                        duration: 600.ms,
                        curve: Curves.elasticOut,
                        begin: const Offset(0.3, 0.3),
                      )
                      .fade(duration: 400.ms),

                  const SizedBox(height: 32),

                  // App Name
                  Text(
                    'eHealthFinder',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 34,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 0.5,
                    ),
                  )
                      .animate(delay: 300.ms)
                      .fadeIn(duration: 500.ms)
                      .slideY(begin: 0.3, duration: 500.ms, curve: Curves.easeOut),

                  const SizedBox(height: 10),

                  Text(
                    AppConstants.appTagline,
                    style: TextStyle(
                      color: Colors.white.withOpacity(0.8),
                      fontSize: 15,
                      fontWeight: FontWeight.w400,
                    ),
                  )
                      .animate(delay: 500.ms)
                      .fadeIn(duration: 500.ms),

                  const SizedBox(height: 60),

                  // Loading dots
                  _LoadingDots().animate(delay: 700.ms).fadeIn(duration: 400.ms),

                  const SizedBox(height: 24),

                  Text(
                    'Bangladesh\'s #1 Health Portal',
                    style: TextStyle(
                      color: Colors.white.withOpacity(0.55),
                      fontSize: 12,
                      letterSpacing: 1.2,
                    ),
                  ).animate(delay: 800.ms).fadeIn(duration: 600.ms),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _circle(double size, Color color) {
    return Container(
      width: size, height: size,
      decoration: BoxDecoration(shape: BoxShape.circle, color: color),
    );
  }
}

class _LoadingDots extends StatefulWidget {
  @override
  State<_LoadingDots> createState() => _LoadingDotsState();
}

class _LoadingDotsState extends State<_LoadingDots>
    with SingleTickerProviderStateMixin {
  late AnimationController _ctrl;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    )..repeat();
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: List.generate(3, (i) {
        return AnimatedBuilder(
          animation: _ctrl,
          builder: (_, __) {
            final phase = (_ctrl.value * 3 - i).clamp(0.0, 1.0);
            final bounce = phase < 0.5 ? phase * 2 : (1 - phase) * 2;
            return Container(
              margin: const EdgeInsets.symmetric(horizontal: 5),
              width: 8,
              height: 8 + bounce * 8,
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.4 + bounce * 0.6),
                borderRadius: BorderRadius.circular(4),
              ),
            );
          },
        );
      }),
    );
  }
}
