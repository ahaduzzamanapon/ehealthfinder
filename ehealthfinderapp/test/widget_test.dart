import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:ehealthfinderapp/main.dart';

void main() {
  testWidgets('App starts with splash screen', (WidgetTester tester) async {
    await tester.pumpWidget(const EHealthFinderApp());
    await tester.pump();
    expect(find.byType(MaterialApp), findsOneWidget);
  });
}
