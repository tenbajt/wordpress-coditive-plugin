import styled from "styled-components";
import { color } from "../../resources";

export const Table = styled.div`
    display: grid;
    gap: 1px;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    background-color: ${color.gray._200};
`;

export const Cell = styled.div`
    display: grid;
    align-content: center;
    gap: 0.25rem;
    padding: 1.5rem;
    background-color: white;
`;

export const Label = styled.span`
    color: ${color.gray._900};
    font-weight: 500;
`;

export const Value = styled.span`
    color: ${color.gray._800};
`;

