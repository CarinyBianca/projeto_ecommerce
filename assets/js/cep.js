document.addEventListener('DOMContentLoaded', function() {
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('logradouro').value = data.logradouro;
                            document.getElementById('bairro').value = data.bairro;
                            document.getElementById('cidade').value = data.localidade;
                            document.getElementById('estado').value = data.uf;
                            // Foca no campo número após preenchimento automático
                            document.getElementById('numero').focus();
                        }
                    })
                    .catch(error => console.error('Erro:', error));
            }
        });
    }

    // Para cálculo de frete no checkout
    const cepEntregaInput = document.getElementById('cep_entrega');
    if (cepEntregaInput) {
        cepEntregaInput.addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                // Primeiro, busca o endereço
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('logradouro_entrega').value = data.logradouro;
                            document.getElementById('bairro_entrega').value = data.bairro;
                            document.getElementById('cidade_entrega').value = data.localidade;
                            document.getElementById('estado_entrega').value = data.uf;
                            
                            // Em seguida, calcula o frete
                            calcularFrete(cep);
                        }
                    })
                    .catch(error => console.error('Erro:', error));
            }
        });
    }
});

function calcularFrete(cep) {
    // Obtém os produtos do carrinho
    fetch('backend/api/calcular_frete.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            cep: cep,
            produtos: window.carrinhoItems || [] // Array com IDs e quantidades
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('valor_frete').textContent = 
                `R$ ${data.frete.toFixed(2).replace('.', ',')}`;
            
            // Atualiza o total
            const subtotal = parseFloat(document.getElementById('subtotal').dataset.valor);
            const total = subtotal + data.frete;
            document.getElementById('total').textContent = 
                `R$ ${total.toFixed(2).replace('.', ',')}`;
        }
    })
    .catch(error => console.error('Erro:', error));
}
