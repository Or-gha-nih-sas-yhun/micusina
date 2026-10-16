import 'dart:convert';
import 'package:flutter/services.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

const brand = Color(0xff9b167d);
const apiBase = String.fromEnvironment('API_URL', defaultValue: 'https://micusina-pos.com');

String plainText(dynamic value, {String fallback = ''}) {
  if (value is! String) return fallback;
  // Flutter's Text widget treats this as text; remove control characters and
  // bound server supplied strings so malformed records cannot overwhelm UI.
  final cleaned = value.replaceAll(RegExp(r'[\u0000-\u0008\u000B\u000C\u000E-\u001F]'), '').trim();
  return cleaned.substring(0, cleaned.length.clamp(0, 500));
}

void main() => runApp(const MiCusinaApp());

class MiCusinaApp extends StatelessWidget {
  const MiCusinaApp({super.key});
  @override
  Widget build(BuildContext context) => MaterialApp(
    debugShowCheckedModeBanner: false,
    title: 'Mi Cusina',
    theme: ThemeData(colorSchemeSeed: brand, useMaterial3: true),
    home: const LoginPage(),
  );
}

class LoginPage extends StatefulWidget { const LoginPage({super.key}); @override State<LoginPage> createState() => _LoginPageState(); }
class _LoginPageState extends State<LoginPage> {
  final email = TextEditingController(); final password = TextEditingController(); final twoFactorCode = TextEditingController();
  final formKey = GlobalKey<FormState>();
  bool loading = false; bool twoFactorRequired = false; String? message;
  Future<void> login() async {
    if (!(formKey.currentState?.validate() ?? false)) return;
    setState(() { loading = true; message = null; });
    try {
      final response = await http.post(Uri.parse('$apiBase/api/mobile/login'), headers: {'Accept': 'application/json'}, body: {'email': email.text.trim(), 'password': password.text, 'device_name': 'Mi Cusina Android', if (twoFactorRequired) 'two_factor_code': twoFactorCode.text.trim()});
      final decoded = jsonDecode(response.body);
      final body = decoded is Map<String, dynamic> ? decoded : <String, dynamic>{};
      if (response.statusCode < 300 && body['token'] != null && mounted) Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => MenuPage(token: body['token'] as String)));
      else if (mounted) setState(() { twoFactorRequired = body['two_factor_required'] == true; message = plainText(body['message'], fallback: 'Unable to sign in. Check your details and try again.'); });
    } on FormatException { if (mounted) setState(() => message = 'The server returned an invalid response. Check API_URL and confirm the server is running.'); }
    catch (_) { if (mounted) setState(() => message = 'Cannot reach the Mi Cusina server. Check API_URL and your network connection.'); }
    if (mounted) setState(() => loading = false);
  }
  @override void dispose() { email.dispose(); password.dispose(); twoFactorCode.dispose(); super.dispose(); }
  @override Widget build(BuildContext context) => Scaffold(backgroundColor: const Color(0xfff7f8fb), body: SafeArea(child: ListView(padding: const EdgeInsets.all(28), children: [
    Image.network('$apiBase/assets/imgs/burger-hero.png', height: 200, errorBuilder: (_, __, ___) => const Icon(Icons.restaurant, size: 120, color: brand)),
    const Text('Welcome to Mi Cusina', textAlign: TextAlign.center, style: TextStyle(fontSize: 30, fontWeight: FontWeight.bold)), const SizedBox(height: 8), const Text('Fresh local favorites, simple ordering, and delivery tracking.', textAlign: TextAlign.center), const SizedBox(height: 28),
    Card(child: Padding(padding: const EdgeInsets.all(22), child: Form(key: formKey, child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [const Text('Sign in', style: TextStyle(fontSize: 25, fontWeight: FontWeight.bold)), TextFormField(controller: email, keyboardType: TextInputType.emailAddress, autofillHints: const [AutofillHints.username], validator: (value) { final v = (value ?? '').trim(); return v.isEmpty ? 'Enter your email address.' : !RegExp(r'^[^\s@]+@[^\s@]+\.[^\s@]+$').hasMatch(v) ? 'Enter a valid email address.' : null; }, decoration: const InputDecoration(labelText: 'Email address')), TextFormField(controller: password, obscureText: true, autofillHints: const [AutofillHints.password], validator: (value) => (value ?? '').isEmpty ? 'Enter your password.' : null, decoration: const InputDecoration(labelText: 'Password')), if (twoFactorRequired) TextFormField(controller: twoFactorCode, keyboardType: TextInputType.number, maxLength: 6, validator: (value) => RegExp(r'^\d{6}$').hasMatch((value ?? '').trim()) ? null : 'Enter the 6-digit authenticator code.', decoration: const InputDecoration(labelText: 'Authenticator code')), if (message != null) Padding(padding: const EdgeInsets.only(top: 12), child: Text(plainText(message), style: const TextStyle(color: Colors.red))), const SizedBox(height: 18), FilledButton(onPressed: loading ? null : login, style: FilledButton.styleFrom(backgroundColor: brand), child: Text(loading ? 'Signing in...' : twoFactorRequired ? 'Verify and sign in' : 'Sign in'))])))),
    TextButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const RegisterPage())), child: const Text('Create customer account')),
  ])));
}

class RegisterPage extends StatefulWidget { const RegisterPage({super.key}); @override State<RegisterPage> createState() => _RegisterPageState(); }
class _RegisterPageState extends State<RegisterPage> {
  final _formKey = GlobalKey<FormState>();
  final phone = TextEditingController();
  @override void dispose() { phone.dispose(); super.dispose(); }
  @override Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('Create Account')),
    body: SafeArea(child: Form(
      key: _formKey,
      autovalidateMode: AutovalidateMode.onUserInteraction,
      child: ListView(padding: const EdgeInsets.all(24), children: [
        const Text('Create customer account', style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
        const SizedBox(height: 16),
        TextFormField(
          controller: phone,
          keyboardType: TextInputType.phone,
          maxLength: 11,
          inputFormatters: [FilteringTextInputFormatter.digitsOnly, LengthLimitingTextInputFormatter(11)],
          validator: (value) => (value ?? '').length == 11 ? null : 'Phone number must contain exactly 11 digits.',
          decoration: const InputDecoration(labelText: 'Phone number', hintText: '09XXXXXXXXX', helperText: 'Enter exactly 11 digits'),
        ),
      ]),
    )),
  );
}

class MenuPage extends StatefulWidget { final String token; const MenuPage({super.key, required this.token}); @override State<MenuPage> createState() => _MenuPageState(); }
class _MenuPageState extends State<MenuPage> {
  List<dynamic> foods = []; bool loading = true;
  Map<String, String> get headers => {'Accept': 'application/json', 'Authorization': 'Bearer ${widget.token}'};
  @override void initState(){super.initState(); load();}
  Future<void> load() async { try { final r=await http.get(Uri.parse('$apiBase/api/mobile/foods')); if(r.statusCode < 300 && mounted) { final body=jsonDecode(r.body); final results=body is Map<String,dynamic> ? body['foods'] : null; setState(()=>foods=results is List ? results : []); } } catch (_) { if(mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content:Text('Could not load the menu. Please try again.'))); } finally { if(mounted)setState(()=>loading=false); } }
  Future<void> add(dynamic food) async { try { final r = await http.post(Uri.parse('$apiBase/api/mobile/cart/${food['id']}'), headers: headers, body: {'quantity':'1'}); if (!mounted) return; final decoded=jsonDecode(r.body); final body=decoded is Map<String,dynamic> ? decoded : <String,dynamic>{}; ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(plainText(body['message'], fallback: 'Unable to add item.')))); } catch (_) { if(mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content:Text('Could not reach the server. Please try again.'))); } }
  Future<void> logout() async { await http.post(Uri.parse('$apiBase/api/mobile/logout'), headers: headers); if(mounted)Navigator.of(context).pushAndRemoveUntil(MaterialPageRoute(builder: (_)=>const LoginPage()), (_)=>false); }
  Future<void> confirmLogout() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Log out?'),
        content: const Text('Are you sure you want to log out?'),
        actions: [
          OutlinedButton(
            onPressed: () => Navigator.pop(dialogContext, false),
            style: OutlinedButton.styleFrom(
              foregroundColor: brand,
              side: const BorderSide(color: brand),
            ),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(dialogContext, true),
            style: FilledButton.styleFrom(
              backgroundColor: Colors.red.shade700,
              foregroundColor: Colors.white,
            ),
            child: const Text('Log Out'),
          ),
        ],
      ),
    );
    if (confirmed == true) await logout();
  }
  @override Widget build(BuildContext c)=>Scaffold(appBar:AppBar(title:const Text('Mi Cusina Menu'), actions:[IconButton(onPressed:()=>Navigator.push(c, MaterialPageRoute(builder:(_)=>CartPage(token:widget.token))), icon:const Icon(Icons.shopping_cart), tooltip:'Cart'), IconButton(onPressed:confirmLogout, icon:const Icon(Icons.logout), tooltip:'Log Out')]),body:loading?const Center(child:CircularProgressIndicator()):foods.isEmpty?const Center(child:Text('No menu items are available yet.')):ListView(children:foods.whereType<Map<String,dynamic>>().map((f){final title=plainText(f['title'],fallback:'Menu item'); final detail=plainText(f['detail']); final image=plainText(f['image_url']); return Card(margin:const EdgeInsets.symmetric(horizontal:12,vertical:6),child:ListTile(leading:image.isEmpty?const CircleAvatar(backgroundColor:Color(0xfff6e6f2),child:Icon(Icons.restaurant,color:brand)):ClipRRect(borderRadius:BorderRadius.circular(8),child:Image.network(image,width:56,height:56,fit:BoxFit.cover,errorBuilder:(_,__,___)=>const CircleAvatar(backgroundColor:Color(0xfff6e6f2),child:Icon(Icons.restaurant,color:brand)))),title:Text(title),subtitle:Text('${detail.isEmpty ? '' : '$detail\n'}P${plainText('${f['price']}',fallback:'0.00')}'),isThreeLine:detail.isNotEmpty,trailing:FilledButton(onPressed:()=>add(f), style:FilledButton.styleFrom(backgroundColor:brand), child:const Text('Add'))));}).toList()));
}

class CartPage extends StatefulWidget { final String token; const CartPage({super.key, required this.token}); @override State<CartPage> createState()=>_CartPageState(); }
class _CartPageState extends State<CartPage> {
  List<dynamic> items=[]; bool loading=true;
  Map<String,String> get headers=>{'Accept':'application/json','Authorization':'Bearer ${widget.token}'};
  @override void initState(){super.initState(); load();}
  Future<void> load() async { try { final r=await http.get(Uri.parse('$apiBase/api/mobile/cart'),headers:headers); if(r.statusCode<300&&mounted)setState(()=>items=(jsonDecode(r.body) as Map<String,dynamic>)['items'] as List<dynamic>); } finally {if(mounted)setState(()=>loading=false);} }
  Future<void> change(dynamic item,int quantity) async { if(quantity < 1){await remove(item);return;} final r=await http.patch(Uri.parse('$apiBase/api/mobile/cart/${item['id']}'),headers:headers,body:{'quantity':'$quantity'}); if(r.statusCode<300) await load(); }
  Future<void> remove(dynamic item) async { final r=await http.delete(Uri.parse('$apiBase/api/mobile/cart/${item['id']}'),headers:headers); if(r.statusCode<300) await load(); }
  @override Widget build(BuildContext c) { final total=items.fold<double>(0,(sum,item)=>sum+(num.tryParse('${item['price']}')?.toDouble()??0)); return Scaffold(appBar:AppBar(title:const Text('Cart')),body:loading?const Center(child:CircularProgressIndicator()):Column(children:[Expanded(child:items.isEmpty?const Center(child:Text('Your cart is empty.')):ListView(children:items.map((item){final qty=int.tryParse('${item['quantity']}')??1;return ListTile(title:Text(item['title']),subtitle:Text('P${item['price']}'),leading:IconButton(onPressed:()=>change(item,qty-1),icon:const Icon(Icons.remove),tooltip:'Remove one'),trailing:Row(mainAxisSize:MainAxisSize.min,children:[Text('$qty'),IconButton(onPressed:()=>change(item,qty+1),icon:const Icon(Icons.add),tooltip:'Add one'),TextButton(onPressed:()=>remove(item),child:const Text('Remove'))]);}).toList())),Padding(padding:const EdgeInsets.all(16),child:Row(mainAxisAlignment:MainAxisAlignment.spaceBetween,children:[Text('Total: P${total.toStringAsFixed(2)}',style:const TextStyle(fontWeight:FontWeight.bold)),TextButton.icon(onPressed:()=>Navigator.pop(context),icon:const Icon(Icons.arrow_back),label:const Text('Back to Menu'))]))])); }
}
