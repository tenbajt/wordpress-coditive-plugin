import * as Styled from "./styles";
import { useState } from "@wordpress/element";

export default function Form(props) {
    const [state, setState] = useState({
        name: "",
        price: "",
        currency: "PLN",
        taxRate: 23,
        isProcessing: false,
        message: null,
        error: false,
    });

    const handleSubmit = (event) => {
        event.preventDefault();

        if (state.isProcessing) return;

        setState(prevState => ({
            ...prevState,
            isProcessing: true,
            message: null,
            error: null
        }));

        fetch(props.url, {
            method: 'POST',
            body: new URLSearchParams({
                action: props.action,
                nonce: props.nonce,
                name: state.name,
                price: state.price,
                currency: state.currency,
                tax_rate: state.taxRate,
            })
        })
        .then(response => response.json())
        .then(result => {
            if (!result.success) throw result.data;

            setState(prevState => ({
                ...prevState,
                isProcessing: false,
                message: result.data.message
            }))
        })
        .catch(error => {
            setState(prevState => ({
                ...prevState,
                isProcessing: false,
                error: error
            }));
            console.log(error);
        });
    }

    const handleChange = ({ target }) => {
        setState(prevState => ({
            ...prevState,
            [target.name]: target.validity.valid ? target.value : state[target.name],
            error: {
                ...prevState.error,
                [target.name]: null
            }
        }));
    }

    return (
        <Styled.Form onSubmit={handleSubmit}>
            <Styled.Label>
                Nazwa produktu
                <Styled.Field name="name" type="text" value={state.name} invalid={state.error?.name} onChange={handleChange} autoComplete="off"/>
                {state.error?.name &&
                    <Styled.ErrorMessage>
                        {state.error?.name}
                    </Styled.ErrorMessage>
                }
            </Styled.Label>
            <Styled.PriceContainer>
                <Styled.Label>
                    Kwota netto
                    <Styled.PriceField name="price" type="text" value={state.price} invalid={state.error?.price} onInput={handleChange} pattern="^([1-9]\d*|[0]),?(,\d{1,2})?$" inputmode="decimal"/>
                    <Styled.CurrencyField name="currency" type="text" value={state.currency} disabled/>
                </Styled.Label>
                <Styled.Label>
                    Stawka VAT
                    <Styled.TaxRateSelector as="select" name="taxRate" value={state.taxRate} invalid={state.error?.taxRate} onChange={handleChange}>
                        <option value="23">23%</option>
                        <option value="22">22%</option>
                        <option value="8">8%</option>
                        <option value="7">7%</option>
                        <option value="5">5%</option>
                        <option value="3">3%</option>
                        <option value="0">0%</option>
                        <option value="zw.">zw.</option>
                        <option value="np.">np.</option>
                        <option value="o.o.">o.o.</option>
                    </Styled.TaxRateSelector>
                </Styled.Label>
                {(state.error?.price || state.error?.currency || state.error?.taxRate) &&
                    <Styled.ErrorMessage>
                        {state.error?.price ?? state.error?.currency ?? state.error?.taxRate}
                    </Styled.ErrorMessage>
                }
            </Styled.PriceContainer>
            {(state.error?.message || state.error?.nonce || state.message) &&
                <Styled.Message invalid={state.error?.message || state.error?.nonce}>
                    {state.error?.message ?? state.error?.nonce ?? state.message}
                </Styled.Message>
            }
            <Styled.SubmitButton type="submit" value="Oblicz"/>
            {state.isProcessing &&
                <Styled.Spinner/>
            }
        </Styled.Form>
    )
}