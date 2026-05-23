import Swal from "sweetalert2";
import Validate from "../components/validate.js";
import Requests from "../components/requests.js";

Inputmask('999.999.999-99').mask('#cad-cpf');
Inputmask('(99) 99999-9999').mask('#cad-telefone');

const mdPreRegister     = document.getElementById('mdPreRegister');
const buttonPreRegister = document.getElementById('buttonPreRegister');
const buttonLogin       = document.getElementById('buttonLogin');

mdPreRegister.addEventListener('click', () => {
    $('#modalPreRegisterUser').modal('show');
});

buttonLogin.addEventListener('click', async () => {
    const valid = Validate.SetForm('form').Validate();
    if (!valid) {
        Swal.fire({
            icon: 'error',
            title: 'Ops...',
            text: 'Preencha os campos corretamente!',
            timer: 2500,
            showConfirmButton: false
        });
        return;
    }

    const requests    = new Requests();
    const originalHTML = buttonLogin.innerHTML;

    try {
        buttonLogin.disabled  = true;
        buttonLogin.innerHTML = '<i class="ti ti-loader-2 ti-spin" style="font-size:16px;"></i> Autenticando...';

        const response = await requests.setForm('form').post('/authentication/auth');

        if (!response.status) {
            Swal.fire({
                icon: 'error',
                title: 'Ops...',
                text: response.msg || 'Não foi possível validar as credenciais, tente novamente!',
                timer: 2500,
                showConfirmButton: false
            });
            return;
        }

        window.location.replace('/');

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Ops...',
            text: error.message || 'Tente novamente mais tarde.',
            timer: 4000,
            showConfirmButton: true
        });
    } finally {
        buttonLogin.disabled  = false;
        buttonLogin.innerHTML = originalHTML;
    }
});

buttonPreRegister.addEventListener('click', async () => {
    const camposModal = [
        document.getElementById('cad-nome'),
        document.getElementById('cad-sobrenome'),
        document.getElementById('cad-cpf'),
        document.getElementById('cad-senha'),
    ];

    const camposVazios = camposModal.some(el => !el.value.trim());
    if (camposVazios) {
        Swal.fire({
            icon: 'error',
            title: 'Ops...',
            text: 'Preencha os campos obrigatórios: Nome, Sobrenome, CPF e Senha!',
            timer: 2500,
            showConfirmButton: false
        });
        return;
    }

    const requests     = new Requests();
    const originalHTML = buttonPreRegister.innerHTML;

    try {
        buttonPreRegister.disabled  = true;
        buttonPreRegister.innerHTML = '<i class="ti ti-loader-2 ti-spin"></i> Cadastrando...';

        const response = await requests.setForm('form').post('/authentication/preregister');

        if (!response.status) {
            Swal.fire({
                icon: 'error',
                title: 'Ops...',
                text: response.msg || 'Não foi possível concluir o cadastro, tente novamente!',
                timer: 2500,
                showConfirmButton: false
            });
            return;
        }

        await Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: response.msg || 'Usuário cadastrado com sucesso!',
            timer: 2500,
            showConfirmButton: false
        });

        $('#modalPreRegisterUser').modal('hide');
        camposModal.forEach(el => el.value = '');
        document.getElementById('cad-rg').value       = '';
        document.getElementById('cad-email').value    = '';
        document.getElementById('cad-telefone').value = '';

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Ops...',
            text: error.message || 'Ocorreu um erro ao cadastrar o usuário!',
            timer: 2500,
            showConfirmButton: false
        });
    } finally {
        buttonPreRegister.disabled  = false;
        buttonPreRegister.innerHTML = originalHTML;
    }
});