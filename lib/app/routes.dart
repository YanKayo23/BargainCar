abstract final class AppRoutes {
  static const home = '/';
  static const login = '/entrar';
  static const register = '/cadastro';
  static const recoverPassword = '/recuperar-senha';
  static const resetPassword = '/nova-senha';
  static const favorites = '/favoritos';
  static const profile = '/perfil';
  static const editProfile = '/perfil/editar';
  static const myListings = '/meus-anuncios';
  static const createListing = '/anuncios/novo';
  static const createStore = '/loja/criar';
  static const store = '/loja';
  static const editStore = '/loja/editar';
  static String listing(String id) => '/anuncios/$id';
  static String editListing(String id) => '/anuncios/$id/editar';
}
