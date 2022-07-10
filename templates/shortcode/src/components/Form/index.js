import * as S from "./styles";
import { useState } from "@wordpress/element";

export default function Form(props) {
    const [state, setState] = useState({
        name: "",
        price: "",
        currency: "PLN",
        taxRate: "23%",
        isProcessing: false,
        result: null,
        error: null,
    });

    const handleSubmit = (event) => {
        event.preventDefault();

        if (state.isProcessing) return;

        setState(prevState => ({
            ...prevState,
            isProcessing: true,
            error: null
        }));

        wp.apiFetch({
            path: props.apiPath,
            method: "POST",
            data: {
                name: state.name,
                price: state.price,
                currency: state.currency,
                taxRate: state.taxRate,
            }
        })
        .then(result => {
            setState(prevState => ({
                ...prevState,
                isProcessing: false,
                result: result
            }));
        })
        .catch(error => {
            setState(prevState => ({
                ...prevState,
                isProcessing: false,
                error: error
            }));
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
        <S.Form onSubmit={handleSubmit}>
            <S.Fieldset disabled={state.isProcessing}>
                <S.Label>
                    Nazwa produktu
                    <S.Field name="name" type="text" value={state.name} invalid={state.error?.data?.params?.name} onChange={handleChange} autoComplete="off"/>
                    {state.error?.data?.params?.name &&
                        <S.ErrorMessage>
                            {state.error?.data?.params?.name}
                        </S.ErrorMessage>
                    }
                </S.Label>
                <S.PriceContainer>
                    <S.Label>
                        Kwota netto
                        <S.PriceField name="price" type="text" value={state.price} invalid={state.error?.data?.params?.price} onInput={handleChange} pattern="^([1-9]\d*|[0]),?(,\d{1,2})?$" inputmode="decimal"/>
                        <S.CurrencyField name="currency" type="text" value={state.currency} disabled/>
                    </S.Label>
                    <S.Label>
                        Stawka VAT
                        <S.TaxRateSelector as="select" name="taxRate" value={state.taxRate} invalid={state.error?.data?.params?.taxRate} onChange={handleChange}>
                            {props.taxRateOptions.map((option, index) => (
                                <option key={index} value={option}>{option}</option>
                            ))}
                        </S.TaxRateSelector>
                    </S.Label>
                    {(state.error?.data?.params?.price || state.error?.data?.params?.currency || state.error?.data?.params?.taxRate) &&
                        <S.ErrorMessage>
                            {state.error?.data?.params?.price ?? state.error?.data?.params?.currency ?? state.error?.data?.params?.taxRate}
                        </S.ErrorMessage>
                    }
                </S.PriceContainer>
                {(state.result) &&
                    <S.Result>
                        {state.result}
                    </S.Result>
                }
                <S.SubmitButton type="submit" disabled={state.isProcessing}>
                    Oblicz
                    {state.isProcessing &&
                        <S.Spinner/>
                    }
                </S.SubmitButton>
            </S.Fieldset>
        </S.Form>
    )
}