class ChamberModel {
  final int id;
  final String hospital;
  final String? hospitalLocation;
  final String? address;
  final String? visitingHour;
  final String? appointmentNumber;

  ChamberModel({
    required this.id,
    required this.hospital,
    this.hospitalLocation,
    this.address,
    this.visitingHour,
    this.appointmentNumber,
  });

  factory ChamberModel.fromJson(Map<String, dynamic> json) {
    return ChamberModel(
      id:                  json['id'] as int,
      hospital:            json['hospital'] ?? 'Private Chamber',
      hospitalLocation:    json['hospital_location'],
      address:             json['address'],
      visitingHour:        json['visiting_hour'],
      appointmentNumber:   json['appointment_number'],
    );
  }
}

class DoctorModel {
  final int id;
  final String name;
  final String? degrees;
  final String? designation;
  final String? workplace;
  final String? experience;
  final String? about;
  final String? specialty;
  final String? location;
  final String? imageUrl;
  final String? url;
  final double rating;
  final int reviewCount;
  final List<ChamberModel> chambers;
  final String? appointmentNumber;
  final String? hospital;

  DoctorModel({
    required this.id,
    required this.name,
    this.degrees,
    this.designation,
    this.workplace,
    this.experience,
    this.about,
    this.specialty,
    this.location,
    this.imageUrl,
    this.url,
    this.rating = 0,
    this.reviewCount = 0,
    this.chambers = const [],
    this.appointmentNumber,
    this.hospital,
  });

  factory DoctorModel.fromJson(Map<String, dynamic> json) {
    return DoctorModel(
      id:          json['id'] as int,
      name:        json['name'] ?? '',
      degrees:     json['degrees'],
      designation: json['designation'],
      workplace:   json['workplace'],
      experience:  json['experience'],
      about:       json['about'],
      specialty:   json['specialty'],
      location:    json['location'],
      imageUrl:    json['image_url'],
      url:         json['url'],
      rating:      (json['rating'] ?? 0).toDouble(),
      reviewCount: json['review_count'] ?? 0,
      appointmentNumber: json['appointment_number'],
      hospital:    json['hospital'],
      chambers:    json['chambers'] != null
          ? (json['chambers'] as List)
              .map((c) => ChamberModel.fromJson(c))
              .toList()
          : [],
    );
  }
}
