import * as S from "./styles";

export default function Table({ meta }) {
    const data = {
        'Adres IP': meta.ip,
        'Data wypełnienia': meta.date,
        'Nazwa produktu': meta.name,
        'Kwota netto': meta.price,
        'Waluta': meta.currency,
        'Stawka VAT': meta.tax_rate,
        'Kwota podatku': meta.tax,
        'Kwota brutto': meta.total,
    };

    return (
        <S.Table>
            {Object.keys(data).map((key, index) => (
                <S.Cell key={index}>
                    <S.Label>
                        {key}
                    </S.Label>
                    <S.Value>
                        {data[key]}
                    </S.Value>
                </S.Cell>
            ))}
        </S.Table>
    )
}