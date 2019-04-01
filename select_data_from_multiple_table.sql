#INNER or CROSS JOIN

The first method to combine tables in MySQL is using the inner join clause. With the inner join, the tables will be joined using values that exist on both tables using defined columns. Illustrated in a Venn diagram form, the form of inner join looks like the following figure:

Retrieve Data From Multiple Tables in MySQL - Inner Join Illustration

In MySQL, we can define the INNER JOIN clause in three ways: (1) using the INNER JOIN clause (2) Using CROSS JOIN clause (3) using only JOIN clause. You are free to choose one of them and should consistent in it, I personally prefer using just JOIN clause.

For example, we’ll display the customers who make orders, run the following query:
```
SELECT c.customer_id, customer_name, order_date, order_amount
FROM customer c
JOIN sales_order s ON c.customer_id = s.customer_id
```
If we use the USING clause, then the query will look like the following:
```
SELECT c.customer_id, customer_name, order_date, order_amount
FROM customer c
JOIN sales_order s USING (customer_id)
```
The results:

+-------------+---------------+------------+--------------+
| customer_id | customer_name | order_date | order_amount |
+-------------+---------------+------------+--------------+
|           1 | Alfa          | 2017-02-22 |           23 |
|           3 | Charlie       | 2017-02-22 |           19 |
|           2 | Beta          | 2017-01-01 |          171 |
|           1 | Alfa          | 2017-02-04 |           31 |
+-------------+---------------+------------+--------------+
Explanations:

Customers named Delta does not appear in the result table, it is because the customer does not make any orders.
Order with order_id 5 also does not appear, this is because that order has customer_id NULL, so it is not connected to any customer.
