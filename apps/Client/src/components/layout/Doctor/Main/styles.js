import styled from 'styled-components';

export const MainContainer = styled.main`
  width: 100%;
  padding: 0 24px 24px;
`;

export const ShiftToolbar = styled.section`
  display: flex;
  align-items: center;
  gap: 28px;
  min-height: 48px;
  margin: 0 -24px 10px;
  padding: 7px 24px;
  background: #f8fafc;
  border-bottom: 1px solid #d8dee5;
  color: #30363d;
  font-size: 0.84rem;

  label {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  select {
    padding: 4px 18px 4px 5px;
    color: inherit;
    background: transparent;
    border: 0;
    font: inherit;
  }

  .shift-time {
    white-space: nowrap;
  }

  .shift-doctor {
    color: #56616d;
    font-size: 0.78rem;
  }

  .shift-message {
    flex-basis: 100%;
    color: #087f5b;
    font-size: 0.78rem;
  }

  .shift-message.is-error {
    color: #b42318;
  }

  button {
    margin-left: auto;
    padding: 7px 14px;
    color: #fff;
    background: #087f8c;
    border: 1px solid #066c77;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    font: inherit;
    font-size: 0.76rem;
    font-weight: 600;
    white-space: nowrap;
    cursor: pointer;
  }

  button:hover {
    background: #066c77;
  }

  button:disabled {
    background: #89949e;
    border-color: #89949e;
    box-shadow: none;
    cursor: not-allowed;
  }

  @media (max-width: 600px) {
    flex-wrap: wrap;
    gap: 6px 14px;
    padding: 8px 16px;

    button {
      margin-left: 0;
    }
  }
`;

export const ShiftDialog = styled.form`
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: grid;
  place-items: center;
  padding: 20px;

  .dialog-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(17, 24, 39, 0.56);
  }

  .dialog-card {
    position: relative;
    z-index: 1;
    width: min(100%, 520px);
    padding: 24px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 18px 50px rgba(0, 0, 0, 0.24);
  }

  h2 {
    margin: 0 0 8px;
    color: #1f2937;
    font-size: 1.25rem;
  }

  p {
    margin: 0 0 18px;
    color: #56616d;
    line-height: 1.5;
  }

  label {
    display: block;
    margin-bottom: 7px;
    color: #30363d;
    font-weight: 600;
  }

  textarea {
    box-sizing: border-box;
    width: 100%;
    min-height: 150px;
    padding: 10px 12px;
    border: 1px solid #b8c1ca;
    border-radius: 6px;
    color: #202833;
    font: inherit;
    resize: vertical;
  }

  textarea:focus {
    outline: 2px solid rgba(8, 127, 140, 0.25);
    border-color: #087f8c;
  }

  .dialog-error {
    margin: 10px 0 0;
    color: #b42318;
    font-size: 0.85rem;
  }

  .dialog-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
  }

  .dialog-actions button {
    padding: 8px 15px;
    border: 1px solid transparent;
    border-radius: 5px;
    font: inherit;
    font-weight: 600;
    cursor: pointer;
  }

  .dialog-actions button:disabled {
    cursor: not-allowed;
    opacity: 0.65;
  }

  .cancel-button {
    color: #30363d;
    background: #f1f3f5;
    border-color: #c7cdd3 !important;
  }

  .confirm-button {
    color: #fff;
    background: #087f8c;
  }

  .confirm-button:hover:not(:disabled) {
    background: #066c77;
  }
`;

export const KPISection = styled.section`
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 18px;

  @media (max-width: 900px) {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  @media (max-width: 480px) {
    grid-template-columns: minmax(0, 1fr);
  }
`;

export const OverviewSection = styled.section`
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  align-items: stretch;
  gap: 20px;
  margin-top: 14px;

  @media (max-width: 850px) {
    grid-template-columns: minmax(0, 1fr);
  }
`;

export const HighPrioritySection = styled.section`
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  align-items: stretch;
  gap: 20px;
  margin-top: 18px;

  @media (max-width: 1100px) {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  @media (max-width: 700px) {
    grid-template-columns: minmax(0, 1fr);
  }
`;