const myDictionary = {
    toner: 'toner',
    paper: 'papel',
    form: 'formulário',
    cartridge: 'cartucho',
    ribbon: 'fita',
    desk: 'escritório',
    others: 'outros',
    operator: 'operador',
    manager: 'gerente',
    admin: 'administrador',
    active: 'ativo',
    inactive: 'inativo',
    in_progress: 'em andamento',
    completed: 'finalizado',
    canceled: 'cancelado',
    refused: 'recusado',
    approved: 'aprovado',
    pending: 'pendente',
    all: 'todas',
    dashboard: 'Dashboard',
    products: 'Produtos',
    purchase: 'Compras',
    order: 'Pedidos',
    user: 'Usuários',
    supplier: 'Fornecedores',
};

export default function useTranslateText(word, lang = 'pt', dictionary = myDictionary) {
    word = word.toLowerCase();
    if (lang === 'en') {
        for (const key in dictionary) {
            if (dictionary[key] === word) {
                return key;
            }
        }
    } else if (lang === 'pt') {
        return dictionary[word];
    }
    return word;
}
