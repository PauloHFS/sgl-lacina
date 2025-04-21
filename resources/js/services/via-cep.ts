const url = (cep: string) => `https://viacep.com.br/ws/${cep}/json/`;
const fetchCep = async (cep: string) => {
  const response = await fetch(url(cep));
  if (!response.ok) {
    throw new Error('Failed to fetch data');
  }
  return response.json();
}