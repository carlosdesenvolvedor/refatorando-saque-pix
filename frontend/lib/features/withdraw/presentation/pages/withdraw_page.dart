import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mask_text_input_formatter/mask_text_input_formatter.dart';
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
  final TextEditingController _dateController = TextEditingController();
  final TextEditingController _timeController = TextEditingController();

  final _dateMask = MaskTextInputFormatter(
    mask: '##/##/####',
    filter: {"#": RegExp(r'[0-9]')},
    type: MaskAutoCompletionType.lazy,
  );
  final _timeMask = MaskTextInputFormatter(
    mask: '##:##',
    filter: {"#": RegExp(r'[0-9]')},
    type: MaskAutoCompletionType.lazy,
  );

  String _pixType = 'email';
  bool _isScheduled = false;

  @override
  void dispose() {
    _amountController.dispose();
    _pixKeyController.dispose();
    _dateController.dispose();
    _timeController.dispose();
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
                                const SizedBox(height: 24),
                                // Switch para ativar agendamento
                                SwitchListTile(
                                  title: const Text('Agendar para o futuro?'),
                                  value: _isScheduled,
                                  activeColor: const Color(0xFF820AD1),
                                  onChanged: (value) {
                                    setState(() {
                                      _isScheduled = value;
                                      if (!value) {
                                        _dateController.clear();
                                        _timeController.clear();
                                      }
                                    });
                                  },
                                ),
                                // Se ativado, mostra os campos de data e hora
                                if (_isScheduled) ...[
                                  const SizedBox(height: 16),
                                  Row(
                                    children: [
                                      Expanded(
                                        child: TextFormField(
                                          controller: _dateController,
                                          inputFormatters: [_dateMask],
                                          keyboardType: TextInputType.number,
                                          decoration: const InputDecoration(
                                            labelText: 'Data',
                                            hintText: 'dd/mm/aaaa',
                                            prefixIcon: Icon(Icons.calendar_today, color: Color(0xFF820AD1)),
                                          ),
                                          validator: (value) {
                                            if (_isScheduled && (value == null || value.isEmpty || value.length != 10)) {
                                              return 'Data inválida';
                                            }
                                            return null;
                                          },
                                        ),
                                      ),
                                      const SizedBox(width: 16),
                                      Expanded(
                                        child: TextFormField(
                                          controller: _timeController,
                                          inputFormatters: [_timeMask],
                                          keyboardType: TextInputType.number,
                                          decoration: const InputDecoration(
                                            labelText: 'Hora',
                                            hintText: 'hh:mm',
                                            prefixIcon: Icon(Icons.access_time, color: Color(0xFF820AD1)),
                                          ),
                                          validator: (value) {
                                            if (_isScheduled && (value == null || value.isEmpty || value.length != 5)) {
                                              return 'Hora inválida';
                                            }
                                            return null;
                                          },
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
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
                                        
                                        String? formattedSchedule;
                                        if (_isScheduled) {
                                          try {
                                            final dateParts = _dateController.text.split('/');
                                            final timeParts = _timeController.text.split(':');
                                            
                                            final dateTime = DateTime(
                                              int.parse(dateParts[2]),
                                              int.parse(dateParts[1]),
                                              int.parse(dateParts[0]),
                                              int.parse(timeParts[0]),
                                              int.parse(timeParts[1]),
                                            );
                                            
                                            formattedSchedule = DateFormat('yyyy-MM-dd HH:mm:ss').format(dateTime);
                                          } catch (e) {
                                            ScaffoldMessenger.of(context).showSnackBar(
                                              const SnackBar(content: Text('Data ou hora inválida')),
                                            );
                                            return;
                                          }
                                        }

                                        context.read<WithdrawBloc>().add(
                                              WithdrawRequested(
                                                accountId: widget.accountId,
                                                amount: amount,
                                                pixType: _pixType,
                                                pixKey: _pixKeyController.text,
                                                schedule: formattedSchedule,
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
