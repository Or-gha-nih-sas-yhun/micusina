import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;

const apiBaseUrl = String.fromEnvironment('API_BASE_URL');
const brandColor = Color(0xff9d251f);

void main() => runApp(const MiCusinaApp());

class ApiException implements Exception {
  const ApiException(this.message);
  final String message;
  @override
  String toString() => message;
}

class ApiClient {
  ApiClient(this.storage);
  final FlutterSecureStorage storage;

  Future<dynamic> request(String method, String path, [Map<String, dynamic>? body]) async {
    final base = apiBaseUrl.replaceFirst(RegExp(r'/$'), '');
    if (base.isEmpty) throw const ApiException('Set API_BASE_URL for this release build.');
    final token = await storage.read(key: 'api_token');
    final request = http.Request(method, Uri.parse('$base/api/mobile$path'))
      ..headers.addAll({
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      });
    if (body != null) request.body = jsonEncode(body);
    try {
      final response = await http.Response.fromStream(
        await http.Client().send(request).timeout(const Duration(seconds: 20)),
      );
      final data = response.body.isEmpty ? <String, dynamic>{} : jsonDecode(response.body);
      if (response.statusCode >= 400) {
        throw ApiException(data is Map ? (data['message'] ?? data['errors'] ?? 'Request failed.').toString() : 'Request failed.');
      }
      return data;
    } on ApiException {
      rethrow;
    } catch (_) {
      throw const ApiException('Could not connect. Check your internet connection and try again.');
    }
  }
}

class MiCusinaApp extends StatefulWidget {
  const MiCusinaApp({super.key});
  @override
  State<MiCusinaApp> createState() => _MiCusinaAppState();
}

class _MiCusinaAppState extends State<MiCusinaApp> {
  final storage = const FlutterSecureStorage();
  late final api = ApiClient(storage);
  Map<String, dynamic>? user;
  bool loading = true;

  @override
  void initState() {
    super.initState();
    restoreSession();
  }

  Future<void> restoreSession() async {
    if (await storage.read(key: 'api_token') != null) {
      try {
        user = Map<String, dynamic>.from((await api.request('GET', '/me'))['user']);
      } on ApiException {
        await storage.delete(key: 'api_token');
      }
    }
    if (mounted) setState(() => loading = false);
  }

  Future<void> signOut() async {
    try {
      await api.request('POST', '/logout');
    } on ApiException {}
    await storage.delete(key: 'api_token');
    if (mounted) setState(() => user = null);
  }

  @override
  Widget build(BuildContext context) => MaterialApp(
        title: 'Mi Cusina',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          useMaterial3: true,
          colorScheme: ColorScheme.fromSeed(seedColor: brandColor),
          scaffoldBackgroundColor: const Color(0xfffffbf8),
          inputDecorationTheme: const InputDecorationTheme(border: OutlineInputBorder()),
        ),
        home: loading
            ? const SplashScreen()
            : user == null
                ? LoginScreen(api: api, onSignedIn: (value) => setState(() => user = value))
                : AppShell(api: api, user: user!, onSignOut: signOut),
      );
}

class SplashScreen extends StatelessWidget {
  const SplashScreen({super.key});
  @override
  Widget build(BuildContext context) => const Scaffold(
        body: Center(child: Column(mainAxisSize: MainAxisSize.min, children: [Icon(Icons.restaurant, color: brandColor, size: 64), SizedBox(height: 18), CircularProgressIndicator()])),
      );
}

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key, required this.api, required this.onSignedIn});
  final ApiClient api;
  final ValueChanged<Map<String, dynamic>> onSignedIn;
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final form = GlobalKey<FormState>();
  final email = TextEditingController();
  final password = TextEditingController();
  bool busy = false;
  bool obscure = true;
  @override
  void dispose() { email.dispose(); password.dispose(); super.dispose(); }

  Future<void> signIn() async {
    if (!(form.currentState?.validate() ?? false)) return;
    setState(() => busy = true);
    try {
      final data = await widget.api.request('POST', '/login', {'email': email.text.trim(), 'password': password.text, 'device_name': 'Mi Cusina Mobile'});
      await widget.api.storage.write(key: 'api_token', value: data['token'].toString());
      widget.onSignedIn(Map<String, dynamic>.from(data['user']));
    } on ApiException catch (error) {
      if (mounted) showMessage(context, error.message);
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(body: SafeArea(child: Center(child: SingleChildScrollView(
    padding: const EdgeInsets.all(24),
    child: ConstrainedBox(constraints: const BoxConstraints(maxWidth: 440), child: Form(key: form, child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
      const CircleAvatar(radius: 42, backgroundColor: brandColor, child: Icon(Icons.restaurant_menu, color: Colors.white, size: 42)),
      const SizedBox(height: 20),
      Text('Welcome to Mi Cusina', textAlign: TextAlign.center, style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold)),
      const SizedBox(height: 8),
      const Text('Sign in with your existing Mi Cusina account.', textAlign: TextAlign.center),
      const SizedBox(height: 28),
      TextFormField(controller: email, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(labelText: 'Email address', prefixIcon: Icon(Icons.email_outlined)), validator: (v) => v == null || !v.contains('@') ? 'Enter a valid email.' : null),
      const SizedBox(height: 14),
      TextFormField(controller: password, obscureText: obscure, onFieldSubmitted: (_) => signIn(), decoration: InputDecoration(labelText: 'Password', prefixIcon: const Icon(Icons.lock_outline), suffixIcon: IconButton(icon: Icon(obscure ? Icons.visibility_outlined : Icons.visibility_off_outlined), onPressed: () => setState(() => obscure = !obscure))), validator: required),
      const SizedBox(height: 24),
      FilledButton.icon(onPressed: busy ? null : signIn, icon: busy ? const SizedBox.square(dimension: 18, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.login), label: Text(busy ? 'Signing in...' : 'Sign in'), style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(52))),
    ]))),
  )));
}

class AppShell extends StatefulWidget {
  const AppShell({super.key, required this.api, required this.user, required this.onSignOut});
  final ApiClient api;
  final Map<String, dynamic> user;
  final Future<void> Function() onSignOut;
  @override
  State<AppShell> createState() => _AppShellState();
}

class _AppShellState extends State<AppShell> {
  int selected = 0;
  bool get staff => widget.user['usertype'] == 'admin' || widget.user['usertype'] == 'staff';
  @override
  Widget build(BuildContext context) {
    final pages = staff
        ? [OperationsPage(api: widget.api), StaffOrdersPage(api: widget.api), InventoryPage(api: widget.api, editable: widget.user['usertype'] == 'admin'), ProfilePage(user: widget.user, onSignOut: widget.onSignOut)]
        : [HomePage(user: widget.user, onOpenMenu: () => setState(() => selected = 1)), MenuPage(api: widget.api), CartPage(api: widget.api), CustomerOrdersPage(api: widget.api), ProfilePage(user: widget.user, onSignOut: widget.onSignOut)];
    final destinations = staff
        ? const [NavigationDestination(icon: Icon(Icons.dashboard_outlined), label: 'Dashboard'), NavigationDestination(icon: Icon(Icons.local_shipping_outlined), label: 'Orders'), NavigationDestination(icon: Icon(Icons.inventory_2_outlined), label: 'Inventory'), NavigationDestination(icon: Icon(Icons.person_outline), label: 'Account')]
        : const [NavigationDestination(icon: Icon(Icons.home_outlined), label: 'Home'), NavigationDestination(icon: Icon(Icons.restaurant_menu_outlined), label: 'Menu'), NavigationDestination(icon: Icon(Icons.shopping_bag_outlined), label: 'Cart'), NavigationDestination(icon: Icon(Icons.receipt_long_outlined), label: 'Orders'), NavigationDestination(icon: Icon(Icons.person_outline), label: 'Account')];
    return Scaffold(body: SafeArea(child: IndexedStack(index: selected, children: pages)), bottomNavigationBar: NavigationBar(selectedIndex: selected, onDestinationSelected: (value) => setState(() => selected = value), destinations: destinations));
  }
}

class HomePage extends StatelessWidget {
  const HomePage({super.key, required this.user, required this.onOpenMenu});
  final Map user;
  final VoidCallback onOpenMenu;
  @override
  Widget build(BuildContext context) => AppPage(title: 'Good day, ${firstName(user['name'])}', subtitle: 'What would you like today?', child: ListView(children: [
    Card(color: brandColor, child: Padding(padding: const EdgeInsets.all(24), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      const Icon(Icons.restaurant, color: Colors.white, size: 38), const SizedBox(height: 18),
      const Text('Fresh food, made for you.', style: TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold)),
      const SizedBox(height: 8), const Text('Order your favorites for delivery around Bantayan Island.', style: TextStyle(color: Colors.white70)),
      const SizedBox(height: 18), FilledButton(onPressed: onOpenMenu, style: FilledButton.styleFrom(backgroundColor: Colors.white, foregroundColor: brandColor), child: const Text('Browse menu')),
    ])),
    const SizedBox(height: 18), Text('Fast, simple, and secure', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
    const SizedBox(height: 10), const Card(child: ListTile(leading: Icon(Icons.verified_user_outlined, color: brandColor), title: Text('Secure account'), subtitle: Text('Your session is protected with a secure access token.'))),
  ]));
}

class MenuPage extends StatefulWidget { const MenuPage({super.key, required this.api}); final ApiClient api; @override State<MenuPage> createState() => _MenuPageState(); }
class _MenuPageState extends State<MenuPage> {
  late Future<dynamic> foods;
  @override void initState() { super.initState(); foods = widget.api.request('GET', '/foods'); }
  void reload() => setState(() => foods = widget.api.request('GET', '/foods'));
  Future<void> add(Map food) async { try { await widget.api.request('POST', '/cart/${food['id']}', {'quantity': 1}); if (mounted) showMessage(context, '${food['title']} added to cart.'); } on ApiException catch (e) { if (mounted) showMessage(context, e.message); } }
  @override Widget build(BuildContext context) => AppPage(title: 'Menu', subtitle: 'Freshly prepared favorites', onRefresh: () async => reload(), child: FutureBuilder<dynamic>(future: foods, builder: (_, snapshot) {
    if (snapshot.connectionState != ConnectionState.done) return const LoadingView();
    if (snapshot.hasError) return ErrorView(error: snapshot.error, onRetry: reload);
    final list = List<Map>.from(snapshot.data['foods']);
    if (list.isEmpty) return const EmptyView(icon: Icons.restaurant_menu, text: 'The menu is being prepared.');
    return ListView.separated(itemCount: list.length, separatorBuilder: (_, __) => const SizedBox(height: 12), itemBuilder: (_, index) {
      final food = list[index]; final stock = number(food['stock']).toInt();
      return Card(child: ListTile(contentPadding: const EdgeInsets.all(12), leading: FoodImage(api: widget.api, item: food), title: Text(food['title'].toString(), style: const TextStyle(fontWeight: FontWeight.w700)), subtitle: Text('${peso(food['price'])} • ${stock > 0 ? '$stock available' : 'Out of stock'}'), trailing: IconButton.filledTonal(onPressed: stock > 0 ? () => add(food) : null, icon: const Icon(Icons.add_shopping_cart))));
    });
  }));
}

class CartPage extends StatefulWidget { const CartPage({super.key, required this.api}); final ApiClient api; @override State<CartPage> createState() => _CartPageState(); }
class _CartPageState extends State<CartPage> {
  late Future<dynamic> cart;
  @override void initState() { super.initState(); cart = widget.api.request('GET', '/cart'); }
  void reload() => setState(() => cart = widget.api.request('GET', '/cart'));
  Future<void> change(Map item, int amount) async { try { if (amount < 1) { await widget.api.request('DELETE', '/cart/${item['id']}'); } else { await widget.api.request('PATCH', '/cart/${item['id']}', {'quantity': amount}); } reload(); } on ApiException catch (e) { if (mounted) showMessage(context, e.message); } }
  @override Widget build(BuildContext context) => AppPage(title: 'Your cart', subtitle: 'Review your order before checkout', onRefresh: () async => reload(), child: FutureBuilder<dynamic>(future: cart, builder: (_, snapshot) {
    if (snapshot.connectionState != ConnectionState.done) return const LoadingView();
    if (snapshot.hasError) return ErrorView(error: snapshot.error, onRetry: reload);
    final list = List<Map>.from(snapshot.data['items']);
    if (list.isEmpty) return const EmptyView(icon: Icons.shopping_bag_outlined, text: 'Your cart is empty.');
    return ListView(children: [
      for (final item in list) Padding(padding: const EdgeInsets.only(bottom: 12), child: Card(child: ListTile(leading: FoodImage(api: widget.api, item: item), title: Text(item['title'].toString()), subtitle: Text('Quantity: ${item['quantity']}'), trailing: Row(mainAxisSize: MainAxisSize.min, children: [IconButton(onPressed: () => change(item, number(item['quantity']).toInt() - 1), icon: const Icon(Icons.remove_circle_outline)), Text(peso(item['price'])), IconButton(onPressed: () => change(item, number(item['quantity']).toInt() + 1), icon: const Icon(Icons.add_circle_outline))]))),
      FilledButton.icon(onPressed: () async { final done = await Navigator.push<bool>(context, MaterialPageRoute(builder: (_) => CheckoutPage(api: widget.api))); if (done == true) reload(); }, icon: const Icon(Icons.lock_outline), label: const Text('Secure checkout')),
    ]);
  }));
}

class CheckoutPage extends StatefulWidget { const CheckoutPage({super.key, required this.api}); final ApiClient api; @override State<CheckoutPage> createState() => _CheckoutPageState(); }
class _CheckoutPageState extends State<CheckoutPage> {
  final form = GlobalKey<FormState>(), name = TextEditingController(), phone = TextEditingController(), barangay = TextEditingController(), purok = TextEditingController(), details = TextEditingController(), reference = TextEditingController();
  String town = 'Bantayan', payment = 'Cash on Delivery'; bool busy = false;
  @override void dispose() { for (final controller in [name, phone, barangay, purok, details, reference]) { controller.dispose(); } super.dispose(); }
  Future<void> checkout() async {
    if (!(form.currentState?.validate() ?? false)) return; setState(() => busy = true);
    try {
      await widget.api.request('POST', '/checkout', {'name': name.text.trim(), 'phone': phone.text.trim(), 'municipality': town, 'barangay': barangay.text.trim(), 'purok': purok.text.trim(), 'address_details': details.text.trim(), 'payment_method': payment, 'payment_reference': reference.text.trim()});
      if (mounted) { showMessage(context, 'Order placed successfully.'); Navigator.pop(context, true); }
    } on ApiException catch (e) { if (mounted) showMessage(context, e.message); } finally { if (mounted) setState(() => busy = false); }
  }
  @override Widget build(BuildContext context) => Scaffold(appBar: AppBar(title: const Text('Checkout')), body: Form(key: form, child: ListView(padding: const EdgeInsets.all(20), children: [
    const Text('Delivery details', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)), const SizedBox(height: 14),
    input(name, 'Full name', required), gap, input(phone, 'Mobile number', required, TextInputType.phone), gap, select(town, const ['Bantayan','Madridejos','Santa Fe'], 'Municipality', (v) => setState(() => town = v!)), gap,
    input(barangay, 'Barangay', required), gap, input(purok, 'Purok', required), gap, input(details, 'Address details (optional)', null), const SizedBox(height: 24),
    const Text('Payment', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)), gap, select(payment, const ['Cash on Delivery','GCash','Bank Transfer'], 'Payment method', (v) => setState(() => payment = v!)),
    if (payment != 'Cash on Delivery') ...[gap, input(reference, 'Payment reference', required)], const SizedBox(height: 28),
    FilledButton(onPressed: busy ? null : checkout, style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(52)), child: Text(busy ? 'Placing order...' : 'Place order')),
  ])));
}

class CustomerOrdersPage extends StatelessWidget { const CustomerOrdersPage({super.key, required this.api}); final ApiClient api; @override Widget build(BuildContext context) => OrdersList(api: api, path: '/orders', title: 'My orders', subtitle: 'Follow your delivery status'); }
class StaffOrdersPage extends StatelessWidget { const StaffOrdersPage({super.key, required this.api}); final ApiClient api; @override Widget build(BuildContext context) => OrdersList(api: api, path: '/staff/orders', title: 'Order management', subtitle: 'Assigned and active deliveries', staff: true); }
class OrdersList extends StatefulWidget { const OrdersList({super.key, required this.api, required this.path, required this.title, required this.subtitle, this.staff = false}); final ApiClient api; final String path, title, subtitle; final bool staff; @override State<OrdersList> createState() => _OrdersListState(); }
class _OrdersListState extends State<OrdersList> {
  late Future<dynamic> orders;
  @override void initState() { super.initState(); orders = widget.api.request('GET', widget.path); }
  void reload() => setState(() => orders = widget.api.request('GET', widget.path));
  Future<void> deliver(Map order) async { try { await widget.api.request('PATCH', '/staff/orders/${order['id']}', {'delivery_status': 'Delivered'}); reload(); } on ApiException catch (e) { if (mounted) showMessage(context, e.message); } }
  @override Widget build(BuildContext context) => AppPage(title: widget.title, subtitle: widget.subtitle, onRefresh: () async => reload(), child: FutureBuilder<dynamic>(future: orders, builder: (_, snapshot) {
    if (snapshot.connectionState != ConnectionState.done) return const LoadingView();
    if (snapshot.hasError) return ErrorView(error: snapshot.error, onRetry: reload);
    final list = List<Map>.from(snapshot.data['orders']);
    if (list.isEmpty) return const EmptyView(icon: Icons.receipt_long_outlined, text: 'No orders to show.');
    return ListView.separated(itemCount: list.length, separatorBuilder: (_, __) => const SizedBox(height: 10), itemBuilder: (_, index) {
      final order = list[index];
      return Card(child: ListTile(title: Text(order['title'].toString(), style: const TextStyle(fontWeight: FontWeight.w700)), subtitle: Text('${order['delivery_status']} • ${order['payment_status']}'), trailing: widget.staff && order['delivery_status'] == 'On The Way' ? FilledButton(onPressed: () => deliver(order), child: const Text('Delivered')) : Text(peso(order['price']))));
    });
  }));
}

class OperationsPage extends StatefulWidget { const OperationsPage({super.key, required this.api}); final ApiClient api; @override State<OperationsPage> createState() => _OperationsPageState(); }
class _OperationsPageState extends State<OperationsPage> {
  late Future<dynamic> dashboard;
  @override void initState() { super.initState(); dashboard = widget.api.request('GET', '/staff/dashboard'); }
  void reload() => setState(() => dashboard = widget.api.request('GET', '/staff/dashboard'));
  @override Widget build(BuildContext context) => AppPage(title: 'Operations dashboard', subtitle: 'Live order and stock overview', onRefresh: () async => reload(), child: FutureBuilder<dynamic>(future: dashboard, builder: (_, snapshot) {
    if (snapshot.connectionState != ConnectionState.done) return const LoadingView();
    if (snapshot.hasError) return ErrorView(error: snapshot.error, onRetry: reload);
    final data = Map<String, dynamic>.from(snapshot.data);
    return GridView.count(crossAxisCount: MediaQuery.of(context).size.width > 600 ? 4 : 2, crossAxisSpacing: 12, mainAxisSpacing: 12, childAspectRatio: 1.3, children: [
      Metric(icon: Icons.pending_actions, label: 'Pending', value: data['pending_orders'].toString()), Metric(icon: Icons.local_shipping_outlined, label: 'On the way', value: data['on_the_way_orders'].toString()), Metric(icon: Icons.task_alt, label: 'Delivered', value: data['delivered_orders'].toString()), Metric(icon: Icons.warning_amber_outlined, label: 'Low stock', value: data['low_stock'].toString()),
    ]);
  }));
}

class InventoryPage extends StatefulWidget { const InventoryPage({super.key, required this.api, required this.editable}); final ApiClient api; final bool editable; @override State<InventoryPage> createState() => _InventoryPageState(); }
class _InventoryPageState extends State<InventoryPage> {
  late Future<dynamic> inventory;
  @override void initState() { super.initState(); inventory = widget.api.request('GET', '/staff/inventory'); }
  void reload() => setState(() => inventory = widget.api.request('GET', '/staff/inventory'));
  @override Widget build(BuildContext context) => AppPage(title: 'Inventory', subtitle: widget.editable ? 'Keep stock levels up to date' : 'Current product availability', onRefresh: () async => reload(), child: FutureBuilder<dynamic>(future: inventory, builder: (_, snapshot) {
    if (snapshot.connectionState != ConnectionState.done) return const LoadingView();
    if (snapshot.hasError) return ErrorView(error: snapshot.error, onRetry: reload);
    final list = List<Map>.from(snapshot.data['foods']);
    return ListView.separated(itemCount: list.length, separatorBuilder: (_, __) => const SizedBox(height: 10), itemBuilder: (_, index) {
      final food = list[index]; final stock = number(food['stock']).toInt();
      return Card(child: ListTile(leading: FoodImage(api: widget.api, item: food), title: Text(food['title'].toString(), style: const TextStyle(fontWeight: FontWeight.w700)), subtitle: Text(stock <= 5 ? 'Low stock: $stock' : '$stock in stock'), trailing: widget.editable ? IconButton(icon: const Icon(Icons.edit_outlined), onPressed: () => edit(food)) : null));
    });
  }));
  Future<void> edit(Map food) async {
    final controller = TextEditingController(text: food['stock'].toString());
    final value = await showDialog<int>(context: context, builder: (_) => AlertDialog(title: Text('Update ${food['title']}'), content: TextField(controller: controller, keyboardType: TextInputType.number), actions: [TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancel')), FilledButton(onPressed: () => Navigator.pop(context, int.tryParse(controller.text)), child: const Text('Save'))]));
    if (value == null || value < 0) return;
    try { await widget.api.request('PATCH', '/staff/inventory/${food['id']}', {'stock': value}); reload(); } on ApiException catch (e) { if (mounted) showMessage(context, e.message); }
  }
}

class ProfilePage extends StatelessWidget {
  const ProfilePage({super.key, required this.user, required this.onSignOut});
  final Map user; final Future<void> Function() onSignOut;
  @override Widget build(BuildContext context) => AppPage(title: 'Account', subtitle: 'Your Mi Cusina profile', child: ListView(children: [
    Card(child: ListTile(leading: CircleAvatar(backgroundColor: brandColor, child: Text(firstName(user['name'])[0].toUpperCase(), style: const TextStyle(color: Colors.white))), title: Text(user['name'].toString(), style: const TextStyle(fontWeight: FontWeight.bold)), subtitle: Text(user['email'].toString()))),
    const SizedBox(height: 14), Card(child: Column(children: [ListTile(leading: const Icon(Icons.phone_outlined), title: const Text('Phone'), subtitle: Text((user['phone'] ?? 'Not provided').toString())), const Divider(height: 1), ListTile(leading: const Icon(Icons.location_on_outlined), title: const Text('Address'), subtitle: Text((user['address'] ?? 'Not provided').toString()))])),
    const SizedBox(height: 24), OutlinedButton.icon(onPressed: onSignOut, icon: const Icon(Icons.logout), label: const Text('Sign out'), style: OutlinedButton.styleFrom(foregroundColor: Colors.red, minimumSize: const Size.fromHeight(50))),
  ]));
}

class AppPage extends StatelessWidget {
  const AppPage({super.key, required this.title, required this.subtitle, required this.child, this.onRefresh});
  final String title, subtitle; final Widget child; final Future<void> Function()? onRefresh;
  @override Widget build(BuildContext context) {
    final body = Padding(padding: const EdgeInsets.fromLTRB(20, 16, 20, 0), child: child);
    return Scaffold(appBar: AppBar(titleSpacing: 20, title: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 20)), Text(subtitle, style: Theme.of(context).textTheme.bodySmall)])), body: onRefresh == null ? body : RefreshIndicator(onRefresh: onRefresh!, child: body));
  }
}
class FoodImage extends StatelessWidget {
  const FoodImage({super.key, required this.api, required this.item});
  final ApiClient api; final Map item;
  @override Widget build(BuildContext context) {
    final image = item['image_url']?.toString() ?? (item['image'] == null ? null : '${apiBaseUrl.replaceFirst(RegExp(r'/$'), '')}/food_img/${item['image']}');
    return ClipRRect(borderRadius: BorderRadius.circular(10), child: SizedBox(width: 58, height: 58, child: image == null ? const ColoredBox(color: Color(0xfff2e7e4), child: Icon(Icons.fastfood)) : Image.network(image, fit: BoxFit.cover, errorBuilder: (_, __, ___) => const ColoredBox(color: Color(0xfff2e7e4), child: Icon(Icons.fastfood)))));
  }
}
class Metric extends StatelessWidget { const Metric({super.key, required this.icon, required this.label, required this.value}); final IconData icon; final String label, value; @override Widget build(BuildContext context) => Card(child: Padding(padding: const EdgeInsets.all(16), child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [Icon(icon, color: brandColor), Text(value, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 25)), Text(label)]))); }
class LoadingView extends StatelessWidget { const LoadingView({super.key}); @override Widget build(BuildContext context) => const Center(child: CircularProgressIndicator()); }
class EmptyView extends StatelessWidget { const EmptyView({super.key, required this.icon, required this.text}); final IconData icon; final String text; @override Widget build(BuildContext context) => Center(child: Column(mainAxisSize: MainAxisSize.min, children: [Icon(icon, color: brandColor, size: 48), const SizedBox(height: 12), Text(text)])); }
class ErrorView extends StatelessWidget { const ErrorView({super.key, required this.error, required this.onRetry}); final Object? error; final VoidCallback onRetry; @override Widget build(BuildContext context) => Center(child: Column(mainAxisSize: MainAxisSize.min, children: [const Icon(Icons.wifi_off_outlined, color: brandColor, size: 48), const SizedBox(height: 12), Text(error.toString(), textAlign: TextAlign.center), const SizedBox(height: 12), OutlinedButton.icon(onPressed: onRetry, icon: const Icon(Icons.refresh), label: const Text('Try again'))])); }
const gap = SizedBox(height: 12);
TextFormField input(TextEditingController controller, String label, String? Function(String?)? validator, [TextInputType? keyboard]) => TextFormField(controller: controller, keyboardType: keyboard, decoration: InputDecoration(labelText: label), validator: validator);
DropdownButtonFormField<String> select(String value, List<String> values, String label, ValueChanged<String?> callback) => DropdownButtonFormField(initialValue: value, decoration: InputDecoration(labelText: label), items: values.map((item) => DropdownMenuItem(value: item, child: Text(item))).toList(), onChanged: callback);
String? required(String? value) => value == null || value.trim().isEmpty ? 'This field is required.' : null;
double number(dynamic value) => double.tryParse(value.toString()) ?? 0;
String peso(dynamic value) => 'PHP ${number(value).toStringAsFixed(2)}';
String firstName(dynamic value) { final name = (value ?? 'Guest').toString().trim(); return name.isEmpty ? 'Guest' : name.split(RegExp(r'\s+')).first; }
void showMessage(BuildContext context, String value) => ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(value), behavior: SnackBarBehavior.floating));
