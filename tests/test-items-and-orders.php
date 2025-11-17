<?php
class Restaurant_Orders_Items_And_Orders_Test extends WP_UnitTestCase {
    public function setUp(): void {
        parent::setUp();
        wp_set_current_user( $this->factory->user->create( [ 'role' => 'administrator' ] ) );
    }

    public function test_item_crud_and_filtering() {
        $request = new WP_REST_Request( 'POST', '/restaurant/v1/items' );
        $request->set_param( 'name', 'Burger' );
        $request->set_param( 'price', 12.5 );
        $request->set_param( 'restaurant_id', 10 );

        $response = rest_get_server()->dispatch( $request );
        $item     = $response->get_data();

        $this->assertSame( 'Burger', $item['name'] );
        $this->assertSame( 10, $item['restaurant_id'] );

        $update = new WP_REST_Request( 'PUT', '/restaurant/v1/items/' . $item['id'] );
        $update->set_param( 'name', 'Cheese Burger' );
        $update->set_param( 'price', 14.0 );
        $update->set_param( 'restaurant_id', 15 );

        $updated = rest_get_server()->dispatch( $update )->get_data();
        $this->assertSame( 'Cheese Burger', $updated['name'] );
        $this->assertSame( 14.0, $updated['price'] );
        $this->assertSame( 15, $updated['restaurant_id'] );

        $filter = new WP_REST_Request( 'GET', '/restaurant/v1/items' );
        $filter->set_param( 'restaurant_id', 15 );
        $filtered = rest_get_server()->dispatch( $filter )->get_data();

        $this->assertCount( 1, $filtered );
        $this->assertSame( $updated['id'], $filtered[0]['id'] );

        $delete = new WP_REST_Request( 'DELETE', '/restaurant/v1/items/' . $item['id'] );
        $deleted = rest_get_server()->dispatch( $delete )->get_data();
        $this->assertTrue( $deleted['deleted'] );
    }

    public function test_order_crud_and_filtering() {
        $request = new WP_REST_Request( 'POST', '/restaurant/v1/orders' );
        $request->set_param( 'name', 'Order A' );
        $request->set_param( 'status', 'pending' );
        $request->set_param( 'items', [ 1, 2 ] );
        $request->set_param( 'restaurant_id', 55 );

        $response = rest_get_server()->dispatch( $request );
        $order    = $response->get_data();

        $this->assertSame( 'pending', $order['status'] );

        $update = new WP_REST_Request( 'PUT', '/restaurant/v1/orders/' . $order['id'] );
        $update->set_param( 'status', 'completed' );

        $updated = rest_get_server()->dispatch( $update )->get_data();
        $this->assertSame( 'completed', $updated['status'] );

        $filter = new WP_REST_Request( 'GET', '/restaurant/v1/orders' );
        $filter->set_param( 'restaurant_id', 55 );
        $filtered = rest_get_server()->dispatch( $filter )->get_data();

        $this->assertCount( 1, $filtered );
        $this->assertSame( $order['id'], $filtered[0]['id'] );

        $delete = new WP_REST_Request( 'DELETE', '/restaurant/v1/orders/' . $order['id'] );
        $deleted = rest_get_server()->dispatch( $delete )->get_data();
        $this->assertTrue( $deleted['deleted'] );
    }
}
