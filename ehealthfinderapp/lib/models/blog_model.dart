class BlogModel {
  final int id;
  final String title;
  final String slug;
  final String? excerpt;
  final String? content;
  final String? category;
  final String? thumbnail;
  final String? publishedAt;
  final String? url;

  BlogModel({
    required this.id,
    required this.title,
    required this.slug,
    this.excerpt,
    this.content,
    this.category,
    this.thumbnail,
    this.publishedAt,
    this.url,
  });

  factory BlogModel.fromJson(Map<String, dynamic> json) {
    return BlogModel(
      id:          json['id'] as int,
      title:       json['title'] ?? '',
      slug:        json['slug'] ?? '',
      excerpt:     json['excerpt'],
      content:     json['content'],
      category:    json['category'],
      thumbnail:   json['thumbnail'],
      publishedAt: json['published_at'],
      url:         json['url'],
    );
  }
}
