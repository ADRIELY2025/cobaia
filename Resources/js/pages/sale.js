import { Validate } from "./Validate.js";
import { Requests } from "./Requests.js";

const Action = document.getElementById('acao');
const Id = document.getElementById('id');
const insertItemButton = document.getElementById('insertItemButton');
const modalPayment = document.getElementById('pagamentoVenda');
const valorPago = document.getElementById('valorPago');
const condicaoPagamento = document.getElementById('condicaoPagamento');

function parseBRLToFloat(value) {
    if (typeof value !== 'string') return 0;

    return parseFloat(
        value
            .replace(/[R$\s]/g, '')
            .replace(/\./g, '')
            .replace(',', '.')
    ) || 0;
}

/* =========================
   RELÓGIO
========================= */
function updateClock() {
    const now = new Date();

    const days = ['Domingo','Segunda-Feira','Terça-Feira','Quarta-Feira','Quinta-Feira','Sexta-Feira','Sábado'];
    const months = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

    const time = document.querySelector('.time');
    const date = document.querySelector('.date');

    if (time) {
        time.textContent = now.toLocaleTimeString('pt-BR');
    }

    if (date) {
        date.textContent = `${days[now.getDay()]}, ${now.getDate()} De ${months[now.getMonth()]} De ${now.getFullYear()}`;
    }
}
setInterval(updateClock, 1000);

/* =========================
   CRIAR / ATUALIZAR VENDA
========================= */
async function InsertSale() {
    const valid = Validate.SetForm('form').Validate();
    if (!valid) return;

    const url = Action.value === 'c'
        ? '/venda/insert'
        : '/venda/update';

    const response = await Requests.SetForm('form').Post(url);

    if (!response.status) return;

    Action.value = 'e';
    Id.value = response.id;

    window.history.pushState({}, '', `/venda/alterar/${response.id}`);

    await listItemSale();
}

/* =========================
   INSERIR ITEM
========================= */
async function InsertItemSale() {
    if (!Id.value) {
        Swal.fire('Erro', 'Venda não iniciada', 'error');
        return;
    }

    const response = await Requests.SetForm('form').Post('/venda/insertitem');

    if (!response.status) return;

    await listItemSale();
}

/* =========================
   DELETAR ITEM
========================= */
async function deleteItem(id) {
    document.getElementById('id_item').value = id;

    const response = await Requests.SetForm('form').Post('/venda/deleteitem');

    if (!response.status) return;

    document.getElementById(`tritem${id}`).remove();

    document.getElementById('total-amount').innerText =
        (response.sale.total_liquido || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    document.getElementById('amount').innerText =
        (response.sale.total_bruto || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    document.getElementById('product-count').innerText =
        `Itens ${response.itens}`;
}

window.deleteItem = deleteItem;

/* =========================
   LISTAR ITENS
========================= */
async function listItemSale() {
    const response = await Requests.SetForm('form').Post('/venda/listitemsale');

    if (!response.status) return;

    const totalLiquido = parseFloat(response.sale.total_liquido || 0);
    const totalBruto = parseFloat(response.sale.total_bruto || 0);

    document.getElementById('total-amount').innerText =
        totalLiquido.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    document.getElementById('amount').innerText =
        totalBruto.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    let html = '';

    response.data.forEach(item => {
        html += `
        <tr id="tritem${item.id}">
            <td>${item.id}</td>
            <td>${item.nome}</td>
            <td>${parseFloat(item.total_liquido).toLocaleString('pt-BR',{style:'currency',currency:'BRL'})}</td>
            <td>
                <button class="btn btn-danger" onclick="deleteItem(${item.id})">
                    Excluir
                </button>
            </td>
        </tr>`;
    });

    document.getElementById('products-table-tbody').innerHTML = html;
    document.getElementById('product-count').innerText = `Itens ${response.data.length}`;
}

/* =========================
   EVENTOS
========================= */
insertItemButton.addEventListener('click', async () => {
    await InsertSale();
    await InsertItemSale();
});

document.addEventListener('DOMContentLoaded', async () => {
    if (Action.value === 'e') {
        await listItemSale();
    }
});

/* =========================
   TECLAS
========================= */
document.addEventListener('keydown', (e) => {
    if (e.key === 'F4') {
        new bootstrap.Modal('#pesquisaProdutoModal').show();
    }

    if (e.key === 'F9') {
        insertItemButton.click();
    }
});

/* =========================
   SELECT PRODUTO
========================= */
$('#pesquisa').select2({
    theme: 'bootstrap-5',
    placeholder: 'Selecione um produto',
    ajax: {
        url: '/produto/listproductdata',
        type: 'POST'
    }
});

/* =========================
   PAGAMENTO
========================= */
modalPayment.addEventListener('shown.bs.modal', async () => {

    const sale = await Requests.SetForm('form').Post('/venda/selectsaledata');

    if (!sale || sale.itens <= 0) {
        Swal.fire('Erro', 'Adicione pelo menos 1 item', 'error');
        return;
    }

    document.getElementById('totalBruto').value = sale.total_bruto;
    document.getElementById('totalLiquido').value = sale.total_liquido;

    valorPago.value = sale.total_liquido;

    const cond = await Requests.SetForm('form').Post('/pagamento/loaddatapayment');

    let html = '';
    cond.data.forEach(i => {
        html += `<option value="${i.id}">${i.titulo}</option>`;
    });

    condicaoPagamento.innerHTML = html;
});