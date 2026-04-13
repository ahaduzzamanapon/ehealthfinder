import 'package:flutter/material.dart';

class AppColors {
  // ── PRIMARY BRAND ─ Indigo/Purple (matches ehealthfinder.com)
  static const primary      = Color(0xFF4F46E5); // Indigo-600 — search btn, hero text
  static const primaryDark  = Color(0xFF3730A3); // Indigo-800 — pressed/dark
  static const primaryLight = Color(0xFFEEF2FF); // Indigo-50  — chip/badge bg

  // ── SECONDARY ─ Teal (logo heart color)
  static const secondary    = Color(0xFF06B6D4); // Cyan-500 — logo teal, doctor accent
  static const secondaryLight = Color(0xFFECFEFF); // Cyan-50

  // ── ACCENT
  static const accent       = Color(0xFFF59E0B); // Amber — rating, price

  // ── BACKGROUNDS
  static const bgLight      = Color(0xFFF8F7FF); // Lavender-white (website bg)
  static const bgCard       = Color(0xFFFFFFFF); // Pure white cards
  static const bgDark       = Color(0xFF1E1B4B); // Navy dark (website dark text)

  // ── TEXT
  static const textDark     = Color(0xFF1E1B4B); // Dark navy (matches website heading)
  static const textMed      = Color(0xFF475569); // Slate-600
  static const textLight    = Color(0xFF94A3B8); // Slate-400

  // ── UTILITY
  static const danger       = Color(0xFFEF4444); // Red
  static const success      = Color(0xFF10B981); // Emerald — green dot badge
  static const border       = Color(0xFFE8E7F8); // Indigo-tinted border
  static const highlight    = Color(0xFF7C3AED); // Violet — for special badges
}

class AppConstants {
  static const baseUrl = 'https://ehealthfinder.com';
  static const apiUrl  = '$baseUrl/api/v1';

  // Local dev:
  // static const apiUrl = 'http://10.0.2.2:8000/api/v1';

  static const appName    = 'eHealthFinder';
  static const appTagline = 'বাংলাদেশের স্বাস্থ্যসেবা প্ল্যাটফর্ম';
}

class AppText {
  static const medicines = 'Medicines';
  static const doctors   = 'Doctors';
  static const blog      = 'Blog';
  static const search    = 'Search medicines, doctors...';
  static const loadMore  = 'Load More';
  static const errorMsg  = 'Something went wrong. Please try again.';
  static const noResults = 'No results found.';
  static const retry     = 'Retry';
  static const back      = 'Back';
  static const callNow   = 'Call Now';
  static const viewAll   = 'View All';
}
