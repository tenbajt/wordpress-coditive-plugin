import styled, { css, keyframes } from "styled-components";
import { breakpoint, color, fontSize, fontWeight, radius } from "../../resources"

export const Form = styled.form`
    width: 100%;
    padding: 1.25rem 1rem;
    overflow: hidden;
    font-family: 
        Inter,
        ui-sans-serif,
        system-ui,
        -apple-system,
        BlinkMacSystemFont,
        "Segoe UI",
        Roboto,
        "Helvetica Neue",
        Arial,
        "Noto Sans",
        sans-serif,
        "Apple Color Emoji",
        "Segoe UI Emoji",
        "Segoe UI Symbol",
        "Noto Color Emoji";
    ${breakpoint.sm} {
        max-width: 21.5rem;
        padding: 1.5rem;
        box-shadow: black 0 0 0 0, black 0 0 0 0, rgba(0, 0, 0, 0.1) 0 1px 3px 0, rgba(0, 0, 0, 0.1) 0 1px 2px -1px;
        border-radius: ${radius.md};
    }
`;

export const Fieldset = styled.fieldset`
    display: grid;
    gap: 1.25rem;
`;

export const Label = styled.label`
    color: ${color.gray._700};
    cursor: pointer;
    position: relative;
    font-size: ${fontSize.sm.size};
    font-weight: ${fontWeight.medium};
    line-height: ${fontSize.sm.height};
`;

export const Field = styled.input`
    width: 100%;
    color: inherit;
    margin: 0.25rem 0 0;
    border: 1px solid ${color.gray._300};
    display: block;
    padding: 0.5rem 0.75rem;
    appearance: none;
    font-family: inherit;
    border-radius: ${radius.md};
    background-color: white;
    &:focus {
        outline: 2px solid transparent;
        box-shadow: white 0 0 0 0, ${color.indigo._500} 0 0 0 1px, black 0 0 0 0;
        border-color: ${color.indigo._500};
        outline-offset: 2px;
    }
    ${breakpoint.sm} {
        font-size: ${fontSize.sm.size};
        line-height: ${fontSize.sm.height};
    }
    ${({ invalid }) => invalid && css`
        &&& {
            outline: 2px solid transparent;
            box-shadow: white 0 0 0 0, red 0 0 0 1px, black 0 0 0 0;
            border-color: red;
            outline-offset: 2px;
        }
    `}
`;

export const ErrorMessage = styled.span`
    color: red;
    margin: 0.25rem 0 0;
    display: inline-block;
    font-size: ${fontSize.xs.size};
    line-height: ${fontSize.xs.height};
`;

export const PriceContainer = styled.div`
    display: grid;
    column-gap: 0.75rem;
    grid-template-columns: 1fr auto;
`;

export const PriceField = styled(Field)`
    padding-right: 3.5rem;
    -moz-appearance: textfield;
    &::-webkit-outer-spin-button,
    &::-webkit-inner-spin-button {
        margin: 0;
        -webkit-appearance: none;
    }
`;

export const CurrencyField = styled(Field)`
    width: 3.5rem;
    position: absolute;
    right: 0;
    bottom: 0;
    box-shadow: none;
    border-color: transparent;
    background-color: transparent;
`;

export const TaxRateSelector = styled(Field)`
    width: 5rem;
`;

export const Result = styled.div`
    color: ${color.gray._800};
    ${breakpoint.sm} {
        font-size: ${fontSize.sm.size};
        line-height: ${fontSize.sm.height};
    }
`;

export const SubmitButton = styled.button`
    width: 100%;
    margin: 0;
    padding: 0.5rem 1rem;
    position: relative;
    display: flex;
    justify-content: center;
    color: white;
    appearance: none;
    font-family: inherit;
    font-weight: ${fontWeight.medium};
    border-radius: ${radius.md};
    background-color: ${color.indigo._600};
    transition: background-color 200ms;
    &:disabled {
        cursor: default;
    }
    &:hover:enabled {
        background-color: ${color.indigo._700};
    }
    &:focus {
        outline: 2px solid transparent;
        outline-offset: 2px;
        box-shadow: white 0 0 0 2px, ${color.indigo._500} 0 0 0 4px, black 0 0 0 0;
    }
    ${breakpoint.sm} {
        font-size: ${fontSize.sm.size};
        line-height: ${fontSize.sm.height};
    }
`;

const spinAnimation = keyframes`
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
`;

export const Spinner = styled.div`
    top: 50%;
    left: 1rem;
    position: absolute;
    display: grid;
    place-content: center;
    transform: translateY(-50%);
    &:after {
        content: "";
        border: 0.125rem solid white;
        border-top: 0.125rem solid ${color.indigo._600};
        border-radius: 50%;
        width: 1.25rem;
        height: 1.25rem;
        animation: ${spinAnimation} 1s linear infinite;
    }
`;