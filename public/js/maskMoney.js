function formatarValorEmReais(valor) {
    if (!valor) return '';
    valor = valor.toString().replace(/\D/g, ''); // Remove tudo que não for número

    // Adiciona o ponto a cada 3 dígitos a partir da direita
    valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');

    // Adiciona a vírgula antes dos dois últimos dígitos
    valor = valor.replace(/(\d{2})$/, ',$1');

    // Adiciona o prefixo "R$"
    return 'R$ ' + valor;
}


document.addEventListener('DOMContentLoaded', function() {

    $('.protocolDisplayMask').on('input', function(e) {
        e.target.value = formatarValorEmReais(e.target.value);
    });
    $('#numberProtocolInput').on('input', function(e) {
        e.target.value = formatarValorEmReais(e.target.value);
    });

    document.querySelectorAll('.protocolDisplayMask').forEach(element => {
        // if(element.nodeName === 'TD')
        //     element.textContent = formatarValorEmReais(element.textContent);
        if(element.nodeName === 'INPUT')
            element.value = formatarValorEmReais(element.value);
        // if(element.nodeName === 'SPAN')
        //     element.textContent = formatarValorEmReais(element.textContent);

    });
});