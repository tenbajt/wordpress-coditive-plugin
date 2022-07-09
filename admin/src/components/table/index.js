import * as Styled from "./styles";

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
        <Styled.Table>
            {Object.keys(data).map((key, index) => (
                <Styled.Cell key={index}>
                    <Styled.Label>
                        {key}
                    </Styled.Label>
                    <Styled.Value>
                        {data[key]}
                    </Styled.Value>
                </Styled.Cell>
            ))}
        </Styled.Table>
    )
}