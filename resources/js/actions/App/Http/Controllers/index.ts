import BookController from './BookController'
import CategoryController from './CategoryController'
import Settings from './Settings'

const Controllers = {
    BookController: Object.assign(BookController, BookController),
    CategoryController: Object.assign(CategoryController, CategoryController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers