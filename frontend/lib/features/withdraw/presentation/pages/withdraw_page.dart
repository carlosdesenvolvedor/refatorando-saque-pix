import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../core/di/service_locator.dart';
import '../bloc/withdraw_bloc.dart';
import '../bloc/withdraw_event.dart';
import '../bloc/withdraw_state.dart';

class WithdrawPage extends StatefulWidget {
  final String accountId;

  const WithdrawPage({super.key, required this.accountId});

  @override
  State<WithdrawPage> createState() => _WithdrawPageState();
}

class _WithdrawPageState extends State<WithdrawPage> {
  final _formKey = GlobalKey<FormState>();
  final TextEditingController _amountController = TextEditingController();
  final TextEditingController _pixKeyController = TextEditingController();
  String _pixType = 'email';

  @override
  void dispose() {
    _amountController.dispose();
    _pixKeyController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: const Text('Realizar Saque'),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.black87),
        titleTextStyle: const TextStyle(
          color: Colors.black87,
          fontSize: 18,
          fontWeight: FontWeight.bold,
        ),
      ),
      body: BlocProvider(
        create: (_) => sl<WithdrawBloc>(),
        child: BlocConsumer<WithdrawBloc, WithdrawState>(
          listener: (context, state) {
            if (state is WithdrawSuccess) {
              FocusScope.of(context).unfocus(); // Close keyboard
              _showSuccessModal(context, _pixKeyController.text);
            } else if (state is WithdrawFailure) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('Erro: ${state.message}'),
                  backgroundColor: Colors.red,
                ),
              );
            }
          },
          builder: (context, state) {
            if (state is WithdrawLoading) {
              return const Center(child: CircularProgressIndicator());
            }

            return LayoutBuilder(
              builder: (context, constraints) {
                return SingleChildScrollView(
                  physics: const ClampingScrollPhysics(),
                  child: ConstrainedBox(
                    constraints: BoxConstraints(
                      minHeight: constraints.maxHeight,
                    ),
                    child: IntrinsicHeight(
                      child: SafeArea(
                        child: Padding(
                          padding: const EdgeInsets.all(24.0),
                          child: Form(
                            key: _formKey,
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'Quanto você quer sacar?',
                                  style: TextStyle(
                                    fontSize: 24,
                                    fontWeight: FontWeight.bold,
                                    color: Colors.black87,
                                  ),
                                ),
                                const SizedBox(height: 32),
                                TextFormField(
                                  controller: _amountController,
                                  style: const TextStyle(
                                    fontSize: 40,
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFF820AD1),
                                  ),
                                  decoration: const InputDecoration(
                                    prefixText: 'R\$ ',
                                    border: InputBorder.none,
                                    hintText: '0,00',
                                    hintStyle: TextStyle(color: Colors.grey),
                                  ),
                                  keyboardType: TextInputType.number,
                                  validator: (value) {
                                    if (value == null || value.isEmpty) {
                                      return 'Informe o valor';
                                    }
                                    if (double.tryParse(value) == null) {
                                      return 'Valor inválido';
                                    }
                                    return null;
                                  },
                                ),
                                const SizedBox(height: 40),
                                Container(
                                  padding: const EdgeInsets.all(16),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFF5F5F7),
                                    borderRadius: BorderRadius.circular(16),
                                  ),
                                  child: Column(
                                    children: [
                                      DropdownButtonFormField<String>(
                                        value: _pixType,
                                        items: const [
                                          DropdownMenuItem(value: 'email', child: Text('E-mail')),
                                          DropdownMenuItem(value: 'cpf', child: Text('CPF')),
                                        ],
                                        onChanged: (value) {
                                          setState(() {
                                            _pixType = value!;
                                            _pixKeyController.clear();
                                          });
                                        },
                                        decoration: const InputDecoration(
                                          labelText: 'Tipo de Chave',
                                          border: InputBorder.none,
                                        ),
                                      ),
                                      const Divider(),
                                      TextFormField(
                                        controller: _pixKeyController,
                                        decoration: InputDecoration(
                                          labelText: 'Chave PIX',
                                          hintText: _pixType == 'email'
                                              ? 'Ex: cliente@teste.com'
                                              : 'Ex: 12345678900',
                                          border: InputBorder.none,
                                        ),
                                        validator: (value) {
                                          if (value == null || value.isEmpty) {
                                            return 'Informe a chave PIX';
                                          }
                                          if (_pixType == 'email' && !value.contains('@')) {
                                            return 'E-mail inválido';
                                          }
                                          if (_pixType == 'cpf' && value.length != 11) {
                                            return 'CPF deve ter 11 números';
                                          }
                                          return null;
                                        },
                                      ),
                                    ],
                                  ),
                                ),
                                const Spacer(),
                                const SizedBox(height: 24),
                                SizedBox(
                                  width: double.infinity,
                                  height: 56,
                                  child: ElevatedButton(
                                    onPressed: () {
                                      if (_formKey.currentState!.validate()) {
                                        final amount =
                                            double.tryParse(_amountController.text) ?? 0.0;
                                        context.read<WithdrawBloc>().add(
                                              WithdrawRequested(
                                                accountId: widget.accountId,
                                                amount: amount,
                                                pixType: _pixType,
                                                pixKey: _pixKeyController.text,
                                              ),
                                            );
                                      }
                                    },
                                    child: const Text('Confirmar Saque'),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }

  void _showSuccessModal(BuildContext context, String pixKey) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return SingleChildScrollView(
          child: Container(
            padding: const EdgeInsets.all(32),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const CircleAvatar(
                  radius: 32,
                  backgroundColor: Color(0xFFE0F7FA),
                  child: Icon(Icons.check, color: Color(0xFF00D793), size: 32),
                ),
                const SizedBox(height: 24),
                const Text(
                  'Saque realizado!',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'O valor foi enviado para a chave:\n$pixKey',
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: Colors.grey),
                ),
                const SizedBox(height: 32),
                SizedBox(
                  width: double.infinity,
                  height: 56,
                  child: ElevatedButton(
                    onPressed: () {
                      Navigator.pop(context); // Close modal
                      Navigator.pop(context); // Back to Home
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFF5F5F7),
                      foregroundColor: Colors.black87,
                      elevation: 0,
                    ),
                    child: const Text('Fechar'),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
