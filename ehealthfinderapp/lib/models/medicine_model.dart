class MedicineModel {
  final int id;
  final String name;
  final String? banglaName;
  final String? dosageForm;
  final String? strength;
  final String? company;
  final String? generic;
  final String? price;
  final bool isAntibiotic;
  final String? imageUrl;
  final String? slug;
  final String? url;
  final Map<String, dynamic>? sections;
  final double rating;
  final int reviewCount;
  final List<MedicineModel> alternatives;

  MedicineModel({
    required this.id,
    required this.name,
    this.banglaName,
    this.dosageForm,
    this.strength,
    this.company,
    this.generic,
    this.price,
    this.isAntibiotic = false,
    this.imageUrl,
    this.slug,
    this.url,
    this.sections,
    this.rating = 0,
    this.reviewCount = 0,
    this.alternatives = const [],
  });

  factory MedicineModel.fromJson(Map<String, dynamic> json) {
    return MedicineModel(
      id:           json['id'] as int,
      name:         json['name'] ?? '',
      banglaName:   json['bangla_name'],
      dosageForm:   json['dosage_form'],
      strength:     json['strength'],
      company:      json['company'],
      generic:      json['generic'],
      price:        json['price']?.toString(),
      isAntibiotic: json['is_antibiotic'] == true,
      imageUrl:     json['image_url'],
      slug:         json['slug'],
      url:          json['url'],
      sections:     json['sections'] != null
          ? Map<String, dynamic>.from(json['sections'])
          : null,
      rating:       (json['rating'] ?? 0).toDouble(),
      reviewCount:  json['review_count'] ?? 0,
      alternatives: json['alternatives'] != null
          ? (json['alternatives'] as List)
              .map((a) => MedicineModel.fromJson(a))
              .toList()
          : [],
    );
  }

  String get displayName => name;
  String get displayPrice => price ?? 'N/A';
  String get displayForm  => dosageForm ?? '';
}
