import 'package:flutter/material.dart';

class AppColors {
  // Brand Colors
  static const primary     = Color(0xFF0EA47A);
  static const primaryDark = Color(0xFF0B8563);
  static const primaryLight= Color(0xFFE6F7F2);
  static const secondary   = Color(0xFF2563EB);
  static const accent      = Color(0xFFF59E0B);

  // Backgrounds
  static const bgLight  = Color(0xFFF8FAFC);
  static const bgCard   = Color(0xFFFFFFFF);
  static const bgDark   = Color(0xFF0F172A);

  // Text
  static const textDark   = Color(0xFF0F172A);
  static const textMed    = Color(0xFF475569);
  static const textLight  = Color(0xFF94A3B8);

  // Utility
  static const danger  = Color(0xFFEF4444);
  static const success = Color(0xFF10B981);
  static const border  = Color(0xFFE2E8F0);
}

class AppConstants {
  static const baseUrl = 'https://ehealthfinder.com';
  static const apiUrl  = '$baseUrl/api/v1';

  // Local dev: static const apiUrl = 'http://10.0.2.2:8000/api/v1';

  static const appName = 'eHealthFinder';
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
