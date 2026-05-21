import Requests from "../components/requests.js";
import Validate from "../components/validate.js";

const Action = document.getElementById('action');
const Id     = document.getElementById('id');
const Insert = document.getElementById('insert');

async function applyChanges() {
    $('button').prop('disabled', true);

    const IsValid = Validate.SetForm('form').Validate();
    if (!IsValid) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Por favor, corrija os erros no formulário antes de salvar.',
            timer: 3000,
            timerProgressBar: true,
        });
        return;
    }

    const requests = new Requests();
    try {
        const response = (Action.value !== 'e')
            ? await requests.setForm('form').post('/produto/insert')
            : await requests.setForm('form').post('/produto/update');

        if (!response.status) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: response.msg || 'Ocorreu um erro ao salvar os dados do produto.',
                timer: 3000,
                timerProgressBar: true,
            });
            return;
        }

        const baseUrl     = window.location.origin;
        const redirectUrl = `${baseUrl}/produto/detalhes/${response.id}`;

        if (Action.value === 'e') {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: response.msg || 'Dados do produto alterados com sucesso.',
                timer: 3000,
                timerProgressBar: true,
            }).then(() => {
                window.location.href = '/produto/lista';
            });
            return;
        }

        Action.value = 'e';
        Id.value     = response.id;
        window.history.pushState({}, '', redirectUrl);

        Swal.fire({
            icon: 'success',
            title: 'Sucesso',
            text: response.msg || 'Produto salvo com sucesso!',
            timer: 3000,
            timerProgressBar: true,
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: `Restrição: ${error.message}`,
            timer: 3000,
            timerProgressBar: true,
        });
        $('button, input, checkbox').prop('disabled', false);
    } finally {
        $('button, input, checkbox').prop('disabled', false);
    }
}

Insert.addEventListener('click', async () => {
    await applyChanges();
});