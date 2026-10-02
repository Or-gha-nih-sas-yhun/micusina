import 'package:flutter_test/flutter_test.dart';
import 'package:mobile_flutter/main.dart';
void main(){testWidgets('Mi Cusina sign in', (tester) async {await tester.pumpWidget(const MiCusinaApp());expect(find.text('Welcome to Mi Cusina'),findsOneWidget);});}
