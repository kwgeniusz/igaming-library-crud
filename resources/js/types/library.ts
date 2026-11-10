export type Category = {
    id: number;
    name: string;
    books_count?: number;
};

export type Book = {
    id: number;
    title: string;
    author: string;
    description: string | null;
    published_year: number;
    category_id: number;
    category: Category;
};
